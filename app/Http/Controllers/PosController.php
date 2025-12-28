<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Stock;
use App\Models\StockLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    /**
     * Display POS page
     */
    public function index()
    {
        $pagetitle = "Point of Sale";
        $customers = Customer::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'phone_number', 'company_name']);

        return view('pos.index', compact('customers', 'pagetitle'));
    }

    /**
     * Search products for POS
     */
    public function search(Request $request)
    {
        $query = $request->q;

        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->where('is_active', true)
            ->with(['units'])
            ->limit(20)
            ->get()
            ->map(function($product) {
                $primaryUnit = $product->units->first();
                $currentStock = $product->current_stock;

                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price ?? $product->price,
                    'stock' => $currentStock,
                    'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                    'primary_unit' => $primaryUnit ? $primaryUnit->name : 'Unit',
                    'primary_unit_id' => $primaryUnit ? $primaryUnit->id : null,
                    'track_stock' => true,
                ];
            });

        return response()->json($products);
    }

    /**
     * Save POS order
     */
    public function savePosOrder(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.sale_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $total = 0;
                $orderItems = [];
                $orderId = 'POS-' . now()->format('Ymd-His') . '-' . Str::random(4);

                // Get or create default stock location
                $defaultLocation = StockLocation::firstOrCreate(
                    ['code' => 'MAIN'],
                    [
                        'name' => 'Main Store',
                        'is_default' => true,
                        'is_active' => true,
                    ]
                );

                // Process each item
                foreach ($request->items as $item) {
                    $product = Product::with('units')->findOrFail($item['product_id']);
                    $selectedUnit = Unit::findOrFail($item['unit_id']);

                    // Calculate how many primary pieces to deduct
                    $piecesToDeduct = $item['qty'];
                    $productUnitPivot = $product->units->where('id', $selectedUnit->id)->first();

                    if ($productUnitPivot && $productUnitPivot->pivot->quantity_per_unit > 0) {
                        $piecesToDeduct = $item['qty'] * $productUnitPivot->pivot->quantity_per_unit;
                    }

                    // Check stock
                    $currentStock = $product->current_stock;
                    if ($currentStock < $piecesToDeduct) {
                        throw new \Exception("Insufficient stock for {$product->title}. Available: {$currentStock} primary units, Required: {$piecesToDeduct}");
                    }

                    $salePrice = $item['sale_price'];
                    $subtotal = $salePrice * $item['qty'];
                    $total += $subtotal;

                    // Prepare order item data (IMPORTANT: includes 'title' to prevent SQL error)
                    $orderItems[] = [
                        'product_id'  => $product->id,
                        'quantity'    => $item['qty'],                    // quantity in selected unit
                        'unit_id'     => $item['unit_id'],                // selected unit
                        'unit_price'  => $salePrice,                      // price per selected unit
                        'total_price' => $subtotal,
                        'title'       => $product->title,                 // Snapshot of product name (fixes 1364 error)
                        'unit_name'   => $selectedUnit->name ?? null,
                    ];

                    // Stock movement: deduct from inventory
                    $previousQuantity = $product->stock;
                    $newQuantity = max(0, $previousQuantity - $piecesToDeduct);

                    Stock::create([
                        'product_id'        => $product->id,
                        'stock_location_id' => $defaultLocation->id,
                        'user_id'           => auth()->id(),
                        'type'              => Stock::TYPE_OUT,
                        'quantity'          => $piecesToDeduct,
                        'previous_quantity' => $previousQuantity,
                        'new_quantity'      => $newQuantity,
                        'reference_type'    => Stock::REFERENCE_SALE,
                        'reference_number'  => $orderId,
                        'notes'             => "POS Sale: Sold {$item['qty']} {$selectedUnit->name}(s) of {$product->title}",
                        'transaction_date'  => now(),
                    ]);

                    // Update product stock
                    $product->decrement('stock', $piecesToDeduct);
                }

                // Create order
                $orderData = [
                    'id'            => $orderId,
                    'customer_id'   => $request->customer_id,
                    'user_id'       => auth()->id(),
                    'total_amount'  => $total,
                    'payment_method'=> $request->payment_method,
                    'order_date'    => now(),
                    'notes'         => 'POS Sale',
                ];

                if (\Schema::hasColumn('orders', 'status')) {
                    $orderData['status'] = 'completed';
                }
                if (\Schema::hasColumn('orders', 'payment_status')) {
                    $orderData['payment_status'] = 'paid';
                }

                $order = Order::create($orderData);

                // Save all order items at once
                $order->items()->createMany($orderItems);

                return response()->json([
                    'success'   => true,
                    'message'   => 'Order completed successfully!',
                    'order_id'  => $orderId,
                    'total'     => $total,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details by barcode
     */
    public function getProductByBarcode($barcode)
    {
        $product = Product::with('units')
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $primaryUnit = $product->units->first();
        $currentStock = $product->current_stock;

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'sale_price' => $product->sale_price ?? $product->price,
                'stock' => $currentStock,
                'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                'primary_unit' => $primaryUnit ? $primaryUnit->name : 'Unit',
                'primary_unit_id' => $primaryUnit ? $primaryUnit->id : null,
                'track_stock' => true,
            ]
        ]);
    }

    /**
     * Generate receipt for POS
     */
    public function generateReceipt($orderId)
    {
        $order = Order::where('id', $orderId)
            ->with(['customer', 'items.product'])
            ->firstOrFail();

        if ($order->user_id != auth()->id() && !auth()->user()->can('Manage pos')) {
            abort(403);
        }

        return view('pos.receipt', compact('order'));
    }

    /**
     * Get today's sales summary
     */
    public function getTodaySales()
    {
        $today = now()->format('Y-m-d');

        $sales = Order::whereDate('order_date', $today)
            ->when(\Schema::hasColumn('orders', 'status'), function($query) {
                return $query->where('status', 'completed');
            })
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_sale'),
                'payment_method'
            ])
            ->groupBy('payment_method')
            ->get();

        $totalSales = $sales->sum('total_sales');
        $totalOrders = $sales->sum('total_orders');

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'total_sales' => $totalSales,
                'average_sale' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
                'by_payment_method' => $sales,
            ]
        ]);
    }

    /**
     * Get product stock
     */
    public function getProductStock($productId)
    {
        $product = Product::findOrFail($productId);
        $currentStock = $product->current_stock;

        return response()->json([
            'success' => true,
            'stock' => $currentStock,
            'product_name' => $product->title
        ]);
    }

    /**
     * Void order (restore stock)
     */
    public function voidOrder($orderId)
    {
        try {
            DB::transaction(function () use ($orderId) {
                $order = Order::with('items.product.units')->where('id', $orderId)->firstOrFail();
                $defaultLocation = StockLocation::firstOrCreate(
                    ['code' => 'MAIN'],
                    ['name' => 'Main Store', 'is_default' => true, 'is_active' => true]
                );

                foreach ($order->items as $item) {
                    $product = $item->product;
                    if (!$product) continue;

                    $piecesToRestore = $item->quantity;
                    if ($item->unit_id) {
                        $unitPivot = $product->units->where('id', $item->unit_id)->first();
                        if ($unitPivot && $unitPivot->pivot->quantity_per_unit > 0) {
                            $piecesToRestore = $item->quantity * $unitPivot->pivot->quantity_per_unit;
                        }
                    }

                    $previousQuantity = $product->stock;
                    $newQuantity = $previousQuantity + $piecesToRestore;

                    Stock::create([
                        'product_id'        => $product->id,
                        'stock_location_id' => $defaultLocation->id,
                        'user_id'           => auth()->id(),
                        'type'              => Stock::TYPE_RETURN,
                        'quantity'          => $piecesToRestore,
                        'previous_quantity' => $previousQuantity,
                        'new_quantity'      => $newQuantity,
                        'reference_type'    => Stock::REFERENCE_RETURN,
                        'reference_number'  => $orderId,
                        'notes'             => "Order #{$orderId} voided - stock restored",
                        'transaction_date'  => now(),
                    ]);

                    $product->increment('stock', $piecesToRestore);
                }

                if (\Schema::hasColumn('orders', 'status')) {
                    $order->update(['status' => 'cancelled']);
                } else {
                    $order->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Order voided successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error voiding order: ' . $e->getMessage()
            ], 500);
        }
    }
}
