<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Stock;
use App\Models\StockLocation;
use App\Models\CustomerPoint;
use App\Models\CustomerPointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $pagetitle = "Point of Sale";
        $customers = Customer::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'phone_number', 'company_name']);

        return view('pos.index', compact('customers', 'pagetitle'));
    }

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
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price ?? $product->price,
                    'stock' => $product->current_stock,
                    'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                    'primary_unit' => $primaryUnit ? $primaryUnit->name : 'Unit',
                    'primary_unit_id' => $primaryUnit ? $primaryUnit->id : null,
                ];
            });

        return response()->json($products);
    }

    public function getCustomerPoints($customerId)
    {
        $points = CustomerPoint::where('customer_id', $customerId)->first();

        return response()->json([
            'success' => true,
            'points' => $points ? $points->points : 0
        ]);
    }

    public function savePosOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.sale_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:percent,fixed',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer',
            'customer_id' => 'nullable|exists:customers,id',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'redeem_points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $subtotal = 0;
                $orderItems = [];
                $orderId = 'POS-' . now()->format('Ymd-His') . '-' . Str::random(4);

                // Fetch default location - no create/update
                $defaultLocation = StockLocation::where('is_default', true)
                    ->orWhere('code', 'MAIN')
                    ->firstOrFail();

                foreach ($request->items as $item) {
                    $product = Product::with('units')->findOrFail($item['product_id']);
                    $selectedUnit = Unit::findOrFail($item['unit_id']);

                    $piecesToDeduct = $item['qty'];
                    $productUnitPivot = $product->units->where('id', $selectedUnit->id)->first();
                    if ($productUnitPivot && $productUnitPivot->pivot->quantity_per_unit > 0) {
                        $piecesToDeduct = $item['qty'] * $productUnitPivot->pivot->quantity_per_unit;
                    }

                    if ($product->current_stock < $piecesToDeduct) {
                        throw new \Exception("Insufficient stock for {$product->title}");
                    }

                    $salePrice = $item['sale_price'];
                    $discountedPrice = $salePrice;
                    $perItemDiscountAmount = 0;

                    if (isset($item['discount_value']) && $item['discount_value'] > 0) {
                        if ($item['discount_type'] === 'percent') {
                            $perItemDiscountAmount = ($salePrice * $item['discount_value']) / 100;
                        } else {
                            $perItemDiscountAmount = $item['discount_value'];
                        }
                        $discountedPrice = max(0, $salePrice - $perItemDiscountAmount);
                    }

                    $lineTotal = $discountedPrice * $item['qty'];
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'product_id'       => $product->id,
                        'quantity'         => $item['qty'],
                        'unit_id'          => $item['unit_id'],
                        'unit_price'       => $salePrice,
                        'discount_type'    => $item['discount_type'] ?? null,
                        'discount_value'   => $item['discount_value'] ?? 0,
                        'discounted_price' => $discountedPrice,
                        'total_price'      => $lineTotal,
                        'title'            => $product->title,
                        'unit_name'        => $selectedUnit->name ?? null,
                    ];

                    // Stock movement
                    $previous = $product->stock;
                    $new = max(0, $previous - $piecesToDeduct);

                    Stock::create([
                        'product_id'        => $product->id,
                        'stock_location_id' => $defaultLocation->id,
                        'user_id'           => auth()->id(),
                        'type'              => Stock::TYPE_OUT,
                        'quantity'          => $piecesToDeduct,
                        'previous_quantity' => $previous,
                        'new_quantity'      => $new,
                        'reference_type'    => Stock::REFERENCE_SALE,
                        'reference_number'  => $orderId,
                        'notes'             => "POS Sale",
                        'transaction_date'  => now(),
                    ]);

                    $product->decrement('stock', $piecesToDeduct);
                }

                // Order-level discount
                $orderDiscountAmount = 0;
                $orderDiscountType = null;
                $orderDiscountValue = 0;

                if ($request->filled('discount_value') && $request->filled('discount_type')) {
                    $orderDiscountType = $request->discount_type;
                    $orderDiscountValue = $request->discount_value;
                    if ($orderDiscountType === 'percent') {
                        $orderDiscountAmount = ($subtotal * $orderDiscountValue) / 100;
                    } else {
                        $orderDiscountAmount = $orderDiscountValue;
                    }
                    $orderDiscountAmount = min($orderDiscountAmount, $subtotal);
                }

                // Points redemption discount
                $pointsRedeemedDiscount = 0;
                $pointsRedeemed = 0;
                if ($request->filled('redeem_points') && $request->customer_id) {
                    $redeemRate = config('loyalty.redeem_rate', 100);
                    $pointsRedeemed = $request->redeem_points;
                    if ($pointsRedeemed % $redeemRate === 0) {
                        $pointsRedeemedDiscount = $pointsRedeemed / $redeemRate;
                        $orderDiscountAmount += $pointsRedeemedDiscount;
                    }
                }

                $grandTotal = max(0, $subtotal - $orderDiscountAmount);

                $orderData = [
                    'id'              => $orderId,
                    'customer_id'     => $request->customer_id ?: null,
                    'user_id'         => auth()->id(),
                    'subtotal'        => $subtotal,
                    'discount_amount' => $orderDiscountAmount,
                    'total_amount'    => $grandTotal,
                    'payment_method'  => $request->payment_method,
                    'order_date'      => now(),
                    'notes'           => 'POS Sale',
                    'status'          => 'completed',
                ];

                if (\Schema::hasColumn('orders', 'payment_status')) {
                    $orderData['payment_status'] = 'paid';
                }

                $order = Order::create($orderData);
                $order->items()->createMany($orderItems);

                // Loyalty Points Earned
                if ($order->customer_id) {
                    $earnRate = config('loyalty.earn_rate', 1);
                    $pointsEarned = floor($order->total_amount * $earnRate);

                    if ($pointsEarned > 0) {
                        $customerPoints = CustomerPoint::firstOrCreate(
                            ['customer_id' => $order->customer_id],
                            ['points' => 0]
                        );

                        $customerPoints->increment('points', $pointsEarned);

                        CustomerPointTransaction::create([
                            'customer_id' => $order->customer_id,
                            'order_id' => $order->id,
                            'points_earned' => $pointsEarned,
                            'amount_spent' => $order->total_amount,
                            'description' => "Earned from order #{$order->id}",
                            'created_by' => auth()->id(),
                        ]);
                    }

                    // Points Redeemed
                    if ($pointsRedeemed > 0) {
                        $customerPoints->decrement('points', $pointsRedeemed);

                        CustomerPointTransaction::create([
                            'customer_id' => $order->customer_id,
                            'order_id' => $order->id,
                            'points_redeemed' => $pointsRedeemed,
                            'discount_applied' => $pointsRedeemedDiscount,
                            'description' => "Redeemed {$pointsRedeemed} points for ₦{$pointsRedeemedDiscount} discount",
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                return response()->json([
                    'success'  => true,
                    'message'  => 'Order completed successfully!',
                    'order_id' => $orderId,
                    'total'    => $grandTotal,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

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

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'sale_price' => $product->sale_price ?? $product->price,
                'stock' => $product->current_stock,
                'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                'primary_unit' => $primaryUnit ? $primaryUnit->name : 'Unit',
                'primary_unit_id' => $primaryUnit ? $primaryUnit->id : null,
            ]
        ]);
    }

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

    public function getProductStock($productId)
    {
        $product = Product::findOrFail($productId);

        return response()->json([
            'success' => true,
            'stock' => $product->current_stock,
            'product_name' => $product->title
        ]);
    }

    public function voidOrder($orderId)
    {
        try {
            DB::transaction(function () use ($orderId) {
                $order = Order::with('items.product.units')->where('id', $orderId)->firstOrFail();

                $defaultLocation = StockLocation::where('is_default', true)
                    ->orWhere('code', 'MAIN')
                    ->firstOrFail();

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
