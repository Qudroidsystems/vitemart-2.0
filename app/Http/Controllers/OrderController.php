<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\User;
use App\Models\Address;
use App\Models\OrderDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'user', 'items']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(15);

        // Add items_count to each order
        foreach ($orders as $order) {
            $order->items_count = $order->items->count();
        }

        // Calculate statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'unpaid' => Order::where('payment_status', 'unpaid')->count(),
        ];

        // Calculate analytics
        $analytics = $this->getAnalytics();

        return view('orders.index', compact('orders', 'stats', 'analytics'));
    }

    /**
     * Get analytics data for dashboard.
     */
    private function getAnalytics()
    {
        // Last 30 days sales
        $salesData = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $dailyTotal = Order::whereDate('created_at', $date->toDateString())
                ->where('payment_status', 'paid')
                ->sum('total_amount');
            $salesData[] = $dailyTotal;
        }

        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'sales_chart' => [
                'labels' => $labels,
                'data' => $salesData,
            ]
        ];
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with([
            'customer',
            'user',
            'items',
            'shippingAddress',
            'billingAddress',
            'transactions',
            'refunds',
            'notes',
            'discounts'
        ])->findOrFail($id);

        // Calculate items count
        $order->items_count = $order->items->count();

        $invoiceDisplay = $order->invoice_number ?? $order->id;
        $pagetitle = "Order #{$invoiceDisplay}";

        return view('orders.show', compact('order', 'pagetitle'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        // Calculate commission if status is delivered
        if ($request->status == 'delivered') {
            $order->calculateAndSaveCommission();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Generate PDF invoice.
     */
    public function invoice($id)
    {
        $order = Order::with([
            'customer',
            'user',
            'items',
            'shippingAddress',
            'billingAddress',
            'discounts'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('orders.invoice', compact('order'));
        return $pdf->download("invoice-{$order->id}.pdf");
    }

    /**
     * Email invoice to customer.
     */
    public function emailInvoice($id)
    {
        $order = Order::with(['customer', 'items'])->findOrFail($id);

        // Get customer email (prefer customer over user)
        $customerEmail = null;
        if ($order->customer) {
            $customerEmail = $order->customer->email;
        } elseif ($order->user) {
            $customerEmail = $order->user->email;
        }

        if (!$customerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No customer email found'
            ], 400);
        }

        $pdf = Pdf::loadView('orders.invoice', compact('order'));

        try {
            Mail::send('emails.invoice', ['order' => $order], function($message) use ($customerEmail, $pdf, $order) {
                $message->to($customerEmail)
                        ->subject('Invoice for Order #' . ($order->invoice_number ?? $order->id))
                        ->attachData($pdf->output(), "invoice-{$order->id}.pdf");
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice emailed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Email invoice failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add note to order.
     */
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string',
            'is_customer_visible' => 'boolean'
        ]);

        $order = Order::findOrFail($id);

        $note = OrderNote::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'is_customer_visible' => $request->has('is_customer_visible')
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Note added successfully']);
        }

        return redirect()->back()->with('success', 'Note added successfully');
    }

    /**
     * Process refund.
     */
    public function refund(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string'
        ]);

        $order = Order::findOrFail($id);

        if ($request->amount > $order->refundableAmount()) {
            return redirect()->back()->with('error', 'Refund amount exceeds refundable amount');
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Refund processed successfully');
    }

    /**
     * Generate packing slip.
     */
    public function packingSlip($id)
    {
        $order = Order::with([
            'customer',
            'items',
            'shippingAddress'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('orders.packing-slip', compact('order'));
        return $pdf->download("packing-slip-{$order->id}.pdf");
    }

    /**
     * Export orders to Excel/CSV.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');

        // Apply filters to export
        $query = Order::with(['customer', 'user', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->get();

        // Add items_count to each order
        foreach ($orders as $order) {
            $order->items_count = $order->items->count();
        }

        $filename = 'orders_export_' . now()->format('Y-m-d_His');

        if ($format === 'csv') {
            return $this->exportCsv($orders, $filename);
        }

        return Excel::download(new OrdersExport($orders), $filename . '.xlsx');
    }

    /**
     * Export to CSV.
     */
    private function exportCsv($orders, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($orders) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Add headers
            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Subtotal',
                'Discount',
                'Total Amount',
                'Status',
                'Payment Status',
                'Payment Method',
                'Items Count',
                'Order Date'
            ]);

            // Add data
            foreach ($orders as $order) {
                $customerName = 'N/A';
                $customerEmail = 'N/A';
                $customerPhone = 'N/A';

                if ($order->customer) {
                    $customerName = $order->customer->first_name . ' ' . $order->customer->last_name;
                    $customerEmail = $order->customer->email ?? 'N/A';
                    $customerPhone = $order->customer->phone_number ?? 'N/A';
                } elseif ($order->user) {
                    $customerName = $order->user->first_name . ' ' . $order->user->last_name;
                    $customerEmail = $order->user->email ?? 'N/A';
                }

                fputcsv($handle, [
                    $order->invoice_number ?? substr($order->id, 0, 8),
                    $customerName,
                    $customerEmail,
                    $customerPhone,
                    $order->subtotal ?? 0,
                    $order->discount_amount ?? 0,
                    $order->total_amount,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method ?? 'N/A',
                    $order->items_count,
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Save POS order.
     */
    public function savePosOrder(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.sale_price' => 'required|numeric|min:0',
            'items.*.unit_id' => 'nullable|integer',
            'items.*.unit_name' => 'nullable|string',
            'items.*.discount_type' => 'nullable|string|in:percent,fixed',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.is_unit_mode' => 'boolean',
            'payment_method' => 'required|string|in:cash,card,transfer',
            'discount_type' => 'nullable|string|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric',
            'tax_cost' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            $totalDiscount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $itemTotal = $item['sale_price'] * $item['qty'];
                $subtotal += $itemTotal;

                // Calculate item discount
                $itemDiscountAmount = 0;
                if (!empty($item['discount_value']) && $item['discount_value'] > 0) {
                    if ($item['discount_type'] === 'percent') {
                        $itemDiscountAmount = $itemTotal * ($item['discount_value'] / 100);
                    } else {
                        $itemDiscountAmount = min($item['discount_value'], $itemTotal);
                    }
                    $totalDiscount += $itemDiscountAmount;
                }

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'title' => $item['title'] ?? 'Product',
                    'unit_price' => $item['sale_price'],
                    'quantity' => $item['qty'],
                    'total_price' => $itemTotal,
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $itemDiscountAmount,
                    'is_unit_mode' => $item['is_unit_mode'] ?? false,
                    'unit_name' => $item['unit_name'] ?? null,
                    'unit_id' => $item['unit_id'] ?? null,
                    'image' => $item['thumbnail'] ?? null,
                ];
            }

            // Apply order-level discount
            $orderDiscountAmount = $request->discount_amount ?? 0;
            if ($orderDiscountAmount > 0) {
                $totalDiscount += $orderDiscountAmount;
            }

            $taxAmount = $request->tax_cost ?? 0;
            $shippingCost = 0; // Default shipping cost
            $grandTotal = max(0, $subtotal + $taxAmount + $shippingCost - $totalDiscount);

            // Generate order ID
            $orderId = 'POS-' . now()->format('Ymd-His') . '-' . uniqid();

            // Create order
            $order = Order::create([
                'id' => $orderId,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'status' => 'completed',
                'payment_status' => $request->payment_method === 'cash' ? 'paid' : 'unpaid',
                'total_amount' => $grandTotal,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_cost' => $taxAmount,
                'discount_amount' => $totalDiscount,
                'payment_method' => $request->payment_method,
                'order_date' => now(),
            ]);

            // Create order items
            foreach ($itemsData as $itemData) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'title' => $itemData['title'],
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'total_price' => $itemData['total_price'],
                    'discount_type' => $itemData['discount_type'],
                    'discount_value' => $itemData['discount_value'],
                    'discount_amount' => $itemData['discount_amount'],
                    'is_unit_mode' => $itemData['is_unit_mode'],
                    'unit_name' => $itemData['unit_name'],
                    'unit_id' => $itemData['unit_id'],
                    'image' => $itemData['image'],
                ]);

                // Save item discount to OrderDiscount table if applicable
                if ($itemData['discount_amount'] > 0) {
                    OrderDiscount::create([
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'type' => 'item',
                        'discount_type' => $itemData['discount_type'],
                        'discount_value' => $itemData['discount_value'],
                        'amount' => $itemData['discount_amount'],
                        'applied_by' => auth()->id(),
                        'applied_at' => now(),
                    ]);
                }
            }

            // Save order-level discount to OrderDiscount table
            if ($orderDiscountAmount > 0) {
                OrderDiscount::create([
                    'order_id' => $order->id,
                    'order_item_id' => null,
                    'type' => 'order',
                    'discount_type' => $request->discount_type ?? 'percent',
                    'discount_value' => $request->discount_value ?? 0,
                    'amount' => $orderDiscountAmount,
                    'applied_by' => auth()->id(),
                    'applied_at' => now(),
                ]);
            }

            // Create transaction
            $transactionStatus = $request->payment_method === 'cash' ? 'success' : 'pending';
            Transaction::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'amount' => $grandTotal,
                'payment_method' => $request->payment_method,
                'status' => $transactionStatus,
                'transaction_id' => 'TXN-' . uniqid(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully',
                'order_id' => $order->id,
                'total' => $grandTotal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Order save failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save order: ' . $e->getMessage()
            ], 500);
        }
    }
}
