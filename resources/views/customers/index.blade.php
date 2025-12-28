@extends('layouts.master')
@section('title', 'Customer Management')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Customer Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Customers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate bg-primary-subtle">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-primary mb-0">Total Customers</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate bg-success-subtle">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-success mb-0">Active</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ number_format($stats['active']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate bg-warning-subtle">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-warning mb-0">Inactive</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ number_format($stats['inactive']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate bg-info-subtle">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-info mb-0">Total Revenue</p>
                            <h4 class="fs-22 fw-semibold mb-0">₦{{ number_format($stats['total_spent'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Actions -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <form action="{{ route('customers.index') }}" method="GET" class="d-flex gap-2">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search by name, email, phone..."
                                           value="{{ request('search') }}" style="min-width: 300px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                            Clear
                                        </a>
                                    @endif
                                </form>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('customers.export') }}" class="btn btn-success">
                                        <i class="bi bi-file-earmark-excel"></i> Export
                                    </a>
                                    @can('Manage customer')
                                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Add Customer
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">All Customers</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Orders</th>
                                            <th>Total Spent</th>
                                            <th>Credit Limit</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customers as $customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                            {{ Str::substr($customer->first_name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h6>
                                                        @if($customer->company_name)
                                                            <small class="text-muted">{{ $customer->company_name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small>{{ $customer->email ?? 'No email' }}</small>
                                                    <small class="text-muted">{{ $customer->phone_number }}</small>
                                                    @if($customer->phone_number_2)
                                                        <small class="text-muted">{{ $customer->phone_number_2 }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $customer->customer_type == 'wholesale' ? 'warning' : ($customer->customer_type == 'corporate' ? 'info' : 'secondary') }}-subtle text-{{ $customer->customer_type == 'wholesale' ? 'warning' : ($customer->customer_type == 'corporate' ? 'info' : 'secondary') }}">
                                                    {{ ucfirst($customer->customer_type) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-semibold">{{ $customer->orders_count }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">₦{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}</span>
                                            </td>
                                            <td>
                                                @if($customer->credit_limit > 0)
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted">Limit: ₦{{ number_format($customer->credit_limit, 2) }}</small>
                                                        <small class="text-muted">Balance: ₦{{ number_format($customer->credit_balance, 2) }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No credit</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $customer->status == 'active' ? 'bg-success-subtle text-success' : ($customer->status == 'suspended' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                                                    {{ ucfirst($customer->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @can('Manage customer')
                                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-people display-1 text-light"></i>
                                                    <h5>No customers found</h5>
                                                    @if(request('search'))
                                                        <p>No results for "{{ request('search') }}"</p>
                                                        <a href="{{ route('customers.index') }}" class="btn btn-primary">View all customers</a>
                                                    @else
                                                        <p>Start by adding your first customer</p>
                                                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                                            <i class="bi bi-plus-circle"></i> Add Customer
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($customers->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted">
                                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        {{ $customers->withQueryString()->links() }}
                                    </nav>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
