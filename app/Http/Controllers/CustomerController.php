<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View customer|Manage customer');
    }

    public function index(Request $request)
    {
        $pagetitle = "Customer Management";

        // Main query with stats
        $query = Customer::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        // Get stats
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'inactive' => Customer::where('status', 'inactive')->count(),
            'suspended' => Customer::where('status', 'suspended')->count(),
            'total_spent' => Customer::withSum('orders', 'total_amount')
                ->get()
                ->sum('orders_sum_total_amount'),
        ];

        return view('customers.index', compact('customers', 'pagetitle', 'stats'));
    }

    public function show(Customer $customer)
    {
        $pagetitle = "Customer Details - {$customer->name}";

        // Load customer with their orders
        $customer->load(['orders' => function($query) {
            $query->latest()->take(10);
        }]);

        // Get customer statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->sum('total_amount'),
            'avg_order_value' => $customer->orders()->avg('total_amount') ?? 0,
            'last_order_date' => $customer->orders()->latest()->first()->created_at ?? null,
        ];

        return view('customers.show', compact('customer', 'pagetitle', 'stats'));
    }

    public function create()
    {
        $pagetitle = "Add New Customer";
        return view('customers.create', compact('pagetitle'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email',
            'phone_number' => 'required|string|unique:customers,phone_number',
            'phone_number_2' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'home_address' => 'nullable|string',
            'office_address' => 'nullable|string',
            'customer_type' => 'required|in:regular,wholesale,corporate',
            'company_name' => 'nullable|string|max:255',
            'tax_id_number' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'loyalty_card_number' => 'nullable|string|unique:customers,loyalty_card_number',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }

    public function edit(Customer $customer)
    {
        $pagetitle = "Edit Customer - {$customer->name}";
        return view('customers.edit', compact('customer', 'pagetitle'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone_number' => 'required|string|unique:customers,phone_number,' . $customer->id,
            'phone_number_2' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'home_address' => 'nullable|string',
            'office_address' => 'nullable|string',
            'customer_type' => 'required|in:regular,wholesale,corporate',
            'status' => 'required|in:active,inactive,suspended',
            'company_name' => 'nullable|string|max:255',
            'tax_id_number' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'loyalty_points' => 'nullable|integer|min:0',
            'loyalty_card_number' => 'nullable|string|unique:customers,loyalty_card_number,' . $customer->id,
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'customers_' . now()->format('Y-m-d') . '.xlsx');
    }
}
