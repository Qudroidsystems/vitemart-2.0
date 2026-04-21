<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders = null)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        if ($this->orders) {
            return $this->orders;
        }

        return Order::with(['customer', 'user', 'items'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('payment_status'), fn($q) => $q->where('payment_status', request('payment_status')))
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Customer',
            'Email',
            'Phone',
            'Date',
            'Items',
            'Total',
            'Payment',
            'Status'
        ];
    }

    public function map($order): array
    {
        // Get customer information (prefer customer over user)
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

        return [
            $order->invoice_number ?? substr($order->id, 0, 8),
            $customerName,
            $customerEmail,
            $customerPhone,
            $order->created_at ? $order->created_at->format('d M Y') : 'N/A',
            $order->items_count ?? $order->items->count(),
            '$' . number_format($order->total_amount, 2),
            ucfirst($order->payment_status),
            ucfirst($order->status),
        ];
    }
}
