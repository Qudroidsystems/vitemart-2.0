<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Unit;
use App\Models\Stock;
use App\Models\StockLocation;
use App\Models\CustomerPoint;
use App\Models\CustomerPointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    // ─── Cache TTLs ───────────────────────────────────────────
    private const INITIAL_PRODUCTS_TTL = 300;   // 5 min
    private const SEARCH_CACHE_TTL     = 120;   // 2 min
    private const UNITS_CACHE_TTL      = 600;   // 10 min

    // ─── Pages ────────────────────────────────────────────────

    public function index()
    {
        $pagetitle = 'Point of Sale';
        $customers = Customer::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'phone_number', 'company_name']);

        return view('pos.index', compact('customers', 'pagetitle'));
    }

    public function grid()
    {
        $pagetitle = 'POS Grid';
        $customers = Customer::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'phone_number']);

        return view('pos.grid', compact('customers', 'pagetitle'));
    }

    // ─── Initial products (grid POS boot) ─────────────────────

    public function getInitialProducts()
    {
        try {
            $products = Cache::remember('pos_initial_products', self::INITIAL_PRODUCTS_TTL, function () {
                return Product::where('is_active', true)
                    ->with([
                        'units:id,name,short_name,is_default',
                        'category:id,name',
                    ])
                    ->select([
                        'id', 'title', 'sku', 'barcode',
                        'price', 'sale_price', 'stock',
                        'thumbnail', 'category_id',
                    ])
                    ->orderBy('title')
                    ->limit(150)
                    ->get()
                    ->map(fn ($p) => $this->formatProductData($p))
                    ->all();
            });

            return response()->json(['products' => $products])
                ->header('Cache-Control', 'private, max-age=60');

        } catch (\Exception $e) {
            \Log::error('POS getInitialProducts: ' . $e->getMessage());
            return response()->json(['products' => [], 'error' => $e->getMessage()]);
        }
    }

    // ─── Search ───────────────────────────────────────────────

    public function search(Request $request)
    {
        $query = trim($request->q ?? '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'pos_search_' . md5(strtolower($query));

        $results = Cache::remember($cacheKey, self::SEARCH_CACHE_TTL, function () use ($query) {
            return $this->performSearch($query);
        });

        return response()->json($results)
            ->header('Cache-Control', 'private, max-age=60');
    }

    private function performSearch(string $query): array
    {
        $isNumeric = ctype_digit($query);

        // ── Fast path: exact barcode ──────────────────────────
        if ($isNumeric && strlen($query) >= 6) {
            $product = Product::where('barcode', $query)
                ->where('is_active', true)
                ->with(['units:id,name,short_name,is_default'])
                ->select(['id','title','sku','barcode','price','sale_price','stock','thumbnail'])
                ->first();

            if ($product) {
                return [$this->formatProductData($product)];
            }

            $variation = ProductVariation::where('barcode', $query)
                ->with(['product.units:id,name,short_name,is_default'])
                ->first();

            if ($variation && $variation->product?->is_active) {
                return [$this->formatVariationData($variation)];
            }
        }

        // ── Text search ───────────────────────────────────────
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query, $isNumeric) {
                if ($isNumeric) {
                    // Numeric: only barcode/sku — uses indexes
                    $q->where('barcode', 'like', "{$query}%")
                      ->orWhere('sku',    'like', "{$query}%");
                } else {
                    // Text: title full-scan + sku/barcode prefix
                    $q->where('title',   'like', "%{$query}%")
                      ->orWhere('sku',   'like', "{$query}%")
                      ->orWhere('barcode','like', "{$query}%");
                }
            })
            ->with(['units:id,name,short_name,is_default'])
            ->select(['id','title','sku','barcode','price','sale_price','stock','thumbnail'])
            ->limit(24)
            ->get()
            ->map(fn ($p) => $this->formatProductData($p));

        $variations = ProductVariation::where(function ($q) use ($query) {
                $q->where('sku',     'like', "{$query}%")
                  ->orWhere('barcode','like', "{$query}%");
            })
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->with(['product.units:id,name,short_name,is_default'])
            ->limit(8)
            ->get()
            ->map(fn ($v) => $this->formatVariationData($v));

        return $products->merge($variations)->take(24)->values()->all();
    }

    // ─── Product units ────────────────────────────────────────

    public function getProductUnits($productId)
    {
        try {
            $cacheKey = "pos_product_units_{$productId}";

            $units = Cache::remember($cacheKey, self::UNITS_CACHE_TTL, function () use ($productId) {
                $product = Product::with('units:id,name,short_name,is_default')->findOrFail($productId);

                if ($product->units->isEmpty()) {
                    return collect([
                        ['id'=>1,'name'=>'Kilogram','short_name'=>'kg','description'=>'Kilogram','is_default'=>true,'quantity_per_unit'=>1],
                        ['id'=>2,'name'=>'Gram','short_name'=>'g','description'=>'Gram','is_default'=>false,'quantity_per_unit'=>0.001],
                        ['id'=>3,'name'=>'Pound','short_name'=>'lb','description'=>'Pound','is_default'=>false,'quantity_per_unit'=>0.453592],
                    ])->all();
                }

                return $product->units->map(fn ($u) => [
                    'id'                => $u->id,
                    'name'              => $u->name,
                    'short_name'        => $u->short_name,
                    'description'       => $u->description ?? '',
                    'is_default'        => (bool) ($u->is_default ?? false),
                    'quantity_per_unit' => $u->pivot->quantity_per_unit ?? 1,
                ])->all();
            });

            return response()->json(['success' => true, 'units' => $units])
                ->header('Cache-Control', 'private, max-age=300');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load units',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Save order ───────────────────────────────────────────

    public function savePosOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required',
            'items.*.variation_id'     => 'nullable|exists:product_variations,id',
            'items.*.is_variation'     => 'nullable|boolean',
            'items.*.qty'              => 'required|numeric|min:0.001',
            'items.*.unit_id'          => 'required|exists:units,id',
            'items.*.sale_price'       => 'required|numeric|min:0',
            'items.*.discount_type'    => 'nullable|in:percent,fixed',
            'items.*.discount_value'   => 'nullable|numeric|min:0',
            'items.*.is_unit_mode'     => 'nullable|boolean',
            'items.*.unit_name'        => 'nullable|string',
            'payment_method'           => 'required|in:cash,card,transfer',
            'customer_id'              => 'nullable|exists:customers,id',
            'discount_type'            => 'nullable|in:percent,fixed',
            'discount_value'           => 'nullable|numeric|min:0',
            'discount_amount'          => 'nullable|numeric|min:0',
            'redeem_points'            => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $subtotal   = 0;
                $orderItems = [];
                $orderId    = 'POS-' . now()->format('Ymd-His') . '-' . Str::random(4);

                $defaultLocation = StockLocation::where('is_default', true)
                    ->orWhere('code', 'MAIN')
                    ->firstOrFail();

                // Pre-load all products + variations in one query each
                $productIds  = collect($request->items)->pluck('product_id')->unique()->all();
                $variationIds = collect($request->items)
                    ->filter(fn ($i) => !empty($i['variation_id']))
                    ->pluck('variation_id')->unique()->all();

                $products   = Product::with(['units'])->whereIn('id', $productIds)->get()->keyBy('id');
                $variations = $variationIds
                    ? ProductVariation::with(['product.units'])->whereIn('id', $variationIds)->get()->keyBy('id')
                    : collect();

                foreach ($request->items as $item) {
                    $isVariation = isset($item['is_variation']) && (bool) $item['is_variation'];

                    if ($isVariation && !empty($item['variation_id'])) {
                        $variation = $variations[$item['variation_id']] ?? ProductVariation::with('product.units')->findOrFail($item['variation_id']);
                        $product   = $variation->product;
                        $price     = $variation->sale_price ?? $variation->price;
                        $stock     = $variation->stock;
                        $sku       = $variation->sku;
                        $barcode   = $variation->barcode;
                        $title     = $product->title . ($variation->attributes ? ' - ' . implode(', ', $variation->attributes) : '');
                    } else {
                        $product   = $products[$item['product_id']] ?? Product::with('units')->findOrFail($item['product_id']);
                        $variation = null;
                        $price     = $product->sale_price ?? $product->price;
                        $stock     = $product->stock;
                        $sku       = $product->sku;
                        $barcode   = $product->barcode;
                        $title     = $product->title;
                    }

                    $selectedUnit  = Unit::findOrFail($item['unit_id']);
                    $quantity      = (float) $item['qty'];
                    $isUnitMode    = (bool) ($item['is_unit_mode'] ?? false);
                    $piecesToDeduct = $quantity;

                    if (!$isUnitMode) {
                        $productUnitPivot = $product->units->where('id', $selectedUnit->id)->first();
                        if ($productUnitPivot && ($productUnitPivot->pivot->quantity_per_unit ?? 0) > 0) {
                            $piecesToDeduct = $quantity * $productUnitPivot->pivot->quantity_per_unit;
                        }
                    }

                    if ($stock < $piecesToDeduct) {
                        throw new \Exception("Insufficient stock for {$title}. Available: {$stock}, Requested: {$piecesToDeduct}");
                    }

                    $salePrice             = (float) $item['sale_price'];
                    $discountedPrice       = $salePrice;
                    $perItemDiscountAmount = 0;

                    if (($item['discount_value'] ?? 0) > 0) {
                        if ($item['discount_type'] === 'percent') {
                            $perItemDiscountAmount = ($salePrice * $item['discount_value']) / 100;
                        } else {
                            $perItemDiscountAmount = $item['discount_value'];
                        }
                        $discountedPrice = max(0, $salePrice - $perItemDiscountAmount);
                    }

                    $lineTotal  = $discountedPrice * $quantity;
                    $subtotal  += $lineTotal;

                    $orderItems[] = [
                        'product_id'       => $product->id,
                        'variation_id'     => $isVariation ? $item['variation_id'] : null,
                        'quantity'         => $quantity,
                        'unit_id'          => $item['unit_id'],
                        'unit_price'       => $salePrice,
                        'discount_type'    => $item['discount_type'] ?? null,
                        'discount_value'   => $item['discount_value'] ?? 0,
                        'discounted_price' => $discountedPrice,
                        'total_price'      => $lineTotal,
                        'title'            => $title,
                        'sku'              => $sku,
                        'barcode'          => $barcode,
                        'unit_name'        => $item['unit_name'] ?? $selectedUnit->name ?? null,
                        'is_unit_mode'     => $isUnitMode,
                    ];

                    // Stock movement
                    $previous = $isVariation ? $variation->stock : $product->stock;
                    $new      = max(0, $previous - $piecesToDeduct);

                    Stock::create([
                        'product_id'        => $product->id,
                        'variation_id'      => $isVariation ? $variation->id : null,
                        'stock_location_id' => $defaultLocation->id,
                        'user_id'           => auth()->id(),
                        'type'              => Stock::TYPE_OUT,
                        'quantity'          => $piecesToDeduct,
                        'previous_quantity' => $previous,
                        'new_quantity'      => $new,
                        'reference_type'    => Stock::REFERENCE_SALE,
                        'reference_number'  => $orderId,
                        'notes'             => 'POS Sale - ' . ($isUnitMode ? 'Unit Mode' : 'Quantity Mode') . ($isVariation ? ' - Variation' : ''),
                        'transaction_date'  => now(),
                    ]);

                    if ($isVariation) {
                        $variation->decrement('stock', $piecesToDeduct);
                    } else {
                        $product->decrement('stock', $piecesToDeduct);
                    }
                }

                // Order-level discount
                $orderDiscountAmount = 0;
                $orderDiscountType   = null;
                $orderDiscountValue  = 0;

                if ($request->filled('discount_amount') && $request->discount_amount > 0) {
                    $orderDiscountAmount = (float) $request->discount_amount;
                    $orderDiscountType   = $request->discount_type;
                    $orderDiscountValue  = $request->discount_value;
                } elseif ($request->filled('discount_value') && $request->filled('discount_type')) {
                    $orderDiscountType  = $request->discount_type;
                    $orderDiscountValue = $request->discount_value;
                    $orderDiscountAmount = $orderDiscountType === 'percent'
                        ? ($subtotal * $orderDiscountValue) / 100
                        : (float) $orderDiscountValue;
                }

                $orderDiscountAmount = min($orderDiscountAmount, $subtotal);

                // Points redemption
                $pointsRedeemedDiscount = 0;
                $pointsRedeemed         = 0;
                if ($request->filled('redeem_points') && $request->customer_id) {
                    $redeemRate             = config('loyalty.redeem_rate', 100);
                    $pointsRedeemed         = (int) $request->redeem_points;
                    $pointsRedeemedDiscount = $pointsRedeemed > 0 ? $pointsRedeemed / $redeemRate : 0;
                    $orderDiscountAmount   += $pointsRedeemedDiscount;
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
                if (\Schema::hasColumn('orders', 'discount_type')) {
                    $orderData['discount_type']  = $orderDiscountType;
                    $orderData['discount_value'] = $orderDiscountValue;
                }

                $order = Order::create($orderData);
                $order->items()->createMany($orderItems);

                // Loyalty points
                if ($order->customer_id) {
                    $earnRate     = config('loyalty.earn_rate', 1);
                    $pointsEarned = (int) floor($order->total_amount * $earnRate);

                    if ($pointsEarned > 0) {
                        $cp = CustomerPoint::firstOrCreate(['customer_id' => $order->customer_id], ['points' => 0]);
                        $cp->increment('points', $pointsEarned);
                        CustomerPointTransaction::create([
                            'customer_id'   => $order->customer_id,
                            'order_id'      => $order->id,
                            'points_earned' => $pointsEarned,
                            'amount_spent'  => $order->total_amount,
                            'description'   => "Earned from order #{$order->id}",
                            'created_by'    => auth()->id(),
                        ]);
                    }

                    if ($pointsRedeemed > 0) {
                        $cp = CustomerPoint::firstOrCreate(['customer_id' => $order->customer_id], ['points' => 0]);
                        $cp->decrement('points', $pointsRedeemed);
                        CustomerPointTransaction::create([
                            'customer_id'     => $order->customer_id,
                            'order_id'        => $order->id,
                            'points_redeemed' => $pointsRedeemed,
                            'discount_applied'=> $pointsRedeemedDiscount,
                            'description'     => "Redeemed {$pointsRedeemed} points",
                            'created_by'      => auth()->id(),
                        ]);
                    }
                }

                $order->calculateAndSaveCommission();

                return [
                    'success'  => true,
                    'message'  => 'Order completed successfully!',
                    'order_id' => $orderId,
                    'total'    => $grandTotal,
                ];
            });

            // Bust caches so next load reflects new stock
            Cache::forget('pos_initial_products');

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Void order ───────────────────────────────────────────

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

                    $isVariation     = !is_null($item->variation_id);
                    $variation       = $isVariation ? ProductVariation::find($item->variation_id) : null;
                    $isUnitMode      = (bool) ($item->is_unit_mode ?? false);
                    $piecesToRestore = $item->quantity;

                    if (!$isUnitMode && $item->unit_id) {
                        $unitPivot = $product->units->where('id', $item->unit_id)->first();
                        if ($unitPivot && ($unitPivot->pivot->quantity_per_unit ?? 0) > 0) {
                            $piecesToRestore = $item->quantity * $unitPivot->pivot->quantity_per_unit;
                        }
                    }

                    $previousQty = $isVariation ? $variation->stock : $product->stock;
                    $newQty      = $previousQty + $piecesToRestore;

                    Stock::create([
                        'product_id'        => $product->id,
                        'variation_id'      => $isVariation ? $variation->id : null,
                        'stock_location_id' => $defaultLocation->id,
                        'user_id'           => auth()->id(),
                        'type'              => Stock::TYPE_RETURN,
                        'quantity'          => $piecesToRestore,
                        'previous_quantity' => $previousQty,
                        'new_quantity'      => $newQty,
                        'reference_type'    => Stock::REFERENCE_RETURN,
                        'reference_number'  => $orderId,
                        'notes'             => "Order #{$orderId} voided",
                        'transaction_date'  => now(),
                    ]);

                    if ($isVariation && $variation) {
                        $variation->increment('stock', $piecesToRestore);
                    } else {
                        $product->increment('stock', $piecesToRestore);
                    }
                }

                if (\Schema::hasColumn('orders', 'status')) {
                    $order->update(['status' => 'cancelled']);
                } else {
                    $order->delete();
                }
            });

            Cache::forget('pos_initial_products');

            return response()->json(['success' => true, 'message' => 'Order voided successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error voiding order: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Receipt ──────────────────────────────────────────────

    public function generateReceipt($orderId)
    {
        $order = Order::where('id', $orderId)
            ->with(['customer', 'items.product', 'items.unit'])
            ->firstOrFail();

        if ($order->user_id != auth()->id() && !auth()->user()->can('Manage pos')) {
            abort(403);
        }

        return view('pos.receipt', compact('order'));
    }

    // ─── Misc helpers ─────────────────────────────────────────

    public function getTodaySales()
    {
        $today = now()->format('Y-m-d');

        $sales = Order::whereDate('order_date', $today)
            ->when(\Schema::hasColumn('orders', 'status'), fn ($q) => $q->where('status', 'completed'))
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_sale'),
                'payment_method',
            ])
            ->groupBy('payment_method')
            ->get();

        $totalSales  = $sales->sum('total_sales');
        $totalOrders = $sales->sum('total_orders');

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders'      => $totalOrders,
                'total_sales'       => $totalSales,
                'average_sale'      => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
                'by_payment_method' => $sales,
            ],
        ]);
    }

    public function getCustomerPoints($customerId)
    {
        $points = CustomerPoint::where('customer_id', $customerId)->first();
        return response()->json(['success' => true, 'points' => $points?->points ?? 0]);
    }

    public function getProductStock($productId)
    {
        $product = Product::findOrFail($productId);
        return response()->json([
            'success'      => true,
            'stock'        => $product->stock,
            'product_name' => $product->title,
        ]);
    }

    public function getProductByBarcode($barcode)
    {
        $product = Product::with('units')
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if ($product) {
            return response()->json(['success' => true, 'product' => $this->formatProductData($product)]);
        }

        $variation = ProductVariation::where('barcode', $barcode)
            ->with(['product.units', 'product'])
            ->first();

        if ($variation && $variation->product?->is_active) {
            return response()->json(['success' => true, 'product' => $this->formatVariationData($variation)]);
        }

        return response()->json(['success' => false, 'message' => 'Product not found'], 404);
    }

    // ─── Format helpers ───────────────────────────────────────

    private function formatProductData($product): array
    {
        $primaryUnit = $product->units->first();
        $stock       = (float) ($product->stock ?? 0);
        $category    = is_object($product->category)
            ? ($product->category->name ?? '')
            : ($product->category ?? '');

        return [
            'id'                   => $product->id,
            'title'                => $product->title,
            'sku'                  => $product->sku ?? '',
            'barcode'              => $product->barcode ?? '',
            'price'                => (float) $product->price,
            'sale_price'           => (float) ($product->sale_price ?? $product->price),
            'stock'                => $stock,
            'thumbnail'            => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
            'primary_unit'         => $primaryUnit?->name ?? 'Unit',
            'primary_unit_id'      => $primaryUnit?->id,
            'category'             => $category,
            'units'                => $product->units->map(fn ($u) => [
                'id'                => $u->id,
                'name'              => $u->name,
                'short_name'        => $u->short_name,
                'description'       => $u->description ?? '',
                'is_default'        => (bool) ($u->is_default ?? false),
                'quantity_per_unit' => $u->pivot->quantity_per_unit ?? 1,
            ])->all(),
            'is_variation'         => false,
            'variation_id'         => null,
            'variation_attributes' => null,
        ];
    }

    private function formatVariationData($variation): array
    {
        $product     = $variation->product;
        $primaryUnit = $product->units->first();

        return [
            'id'                   => $product->id,
            'variation_id'         => $variation->id,
            'title'                => $product->title . ($variation->attributes ? ' - ' . implode(', ', $variation->attributes) : ''),
            'sku'                  => $variation->sku ?? '',
            'barcode'              => $variation->barcode ?? '',
            'price'                => (float) $variation->price,
            'sale_price'           => (float) ($variation->sale_price ?? $variation->price),
            'stock'                => (float) $variation->stock,
            'thumbnail'            => $variation->image
                ? asset('storage/' . $variation->image)
                : ($product->thumbnail ? asset('storage/' . $product->thumbnail) : null),
            'primary_unit'         => $primaryUnit?->name ?? 'Unit',
            'primary_unit_id'      => $primaryUnit?->id,
            'category'             => '',
            'units'                => $product->units->map(fn ($u) => [
                'id'                => $u->id,
                'name'              => $u->name,
                'short_name'        => $u->short_name,
                'description'       => $u->description ?? '',
                'is_default'        => (bool) ($u->is_default ?? false),
                'quantity_per_unit' => $u->pivot->quantity_per_unit ?? 1,
            ])->all(),
            'is_variation'         => true,
            'variation_attributes' => $variation->attributes,
        ];
    }

    // kept for backward-compat (called from formatProductResponse / formatVariationResponse paths below)
    private function formatProductResponse($product)
    {
        return response()->json([$this->formatProductData($product)]);
    }

    private function formatVariationResponse($variation)
    {
        return response()->json([$this->formatVariationData($variation)]);
    }
}
