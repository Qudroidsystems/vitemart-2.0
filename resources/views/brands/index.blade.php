{{-- resources/views/brands/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Brands Management')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Brands</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">E-commerce</a></li>
                                <li class="breadcrumb-item active">Brands</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Products per Brand</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="brandChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brands Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card" id="brandList">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">All Brands</h5>
                            <div class="flex-shrink-0">
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                        Delete Selected
                                    </button>
                                    @can('Create brand')
                                        <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal">
                                            Add Brand
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                                </div>
                                            </th>
                                            <th>#</th>
                                            <th>Logo</th>
                                            <th>Brand Name</th>
                                            <th>Categories</th>
                                            <th>Products</th>
                                            <th>Featured</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @forelse($data as $brand)
                                        <tr class="border-bottom border-light-subtle">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="{{ $brand->id }}">
                                                </div>
                                            </td>
                                            <td class="fw-medium">{{ $loop->iteration }}</td>
                                            <td>
                                                @if($brand->logo && \Storage::disk('public')->exists($brand->logo))
                                                    <img src="{{ asset('storage/' . $brand->logo) }}"
                                                         class="avatar-lg rounded object-fit-cover"
                                                         alt="{{ $brand->name }}"
                                                         width="50" height="50">
                                                @else
                                                    <div class="avatar-lg bg-light rounded d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-building text-muted fs-3"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $brand->name }}</strong></td>
                                            <td>
                                                @if($brand->categories->count())
                                                    @foreach($brand->categories->take(2) as $c)
                                                        <span class="badge bg-info-subtle text-info me-1">{{ $c->name }}</span>
                                                    @endforeach
                                                    @if($brand->categories->count() > 2)
                                                        <small class="text-muted">+{{ $brand->categories->count()-2 }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $brand->products_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $brand->is_featured ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $brand->is_featured ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td>
                                                <ul class="list-inline hstack gap-2 mb-0">
                                                    @can('Update brand')
                                                        <li>
                                                            <button class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="{{ $brand->id }}">
                                                                <i class="ph-pencil"></i>
                                                            </button>
                                                        </li>
                                                    @endcan
                                                    @can('Delete brand')
                                                        <li>
                                                            <button class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="{{ $brand->id }}">
                                                                <i class="ph-trash"></i>
                                                            </button>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">No brands found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                {{ $data->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="showModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="brandForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="brand_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Brand Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <div class="mt-2">
                            <img id="logo_preview" class="rounded shadow-sm" style="max-height:120px; display:none;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categories</label>
                        <select class="form-select" name="categories[]" id="categories_select" multiple>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
                        <label class="form-check-label">Featured Brand</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteRecordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="bi bi-trash text-danger display-4"></i>
                <h4 class="mt-4">Delete Brand?</h4>
                <p class="text-muted">This action cannot be undone.</p>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="delete-record">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="{{ asset('theme/layouts/assets/libs/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/list.js/list.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set CSRF token for axios
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

    // Chart
    const chartLabels = @json($chart_labels);
    const chartData = @json($chart_data);

    if (document.getElementById('brandChart') && chartLabels.length > 0) {
        new Chart(document.getElementById('brandChart'), {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Products',
                    data: chartData,
                    backgroundColor: '#405189'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // List.js for pagination (optional, you can remove if using Laravel pagination)
    try {
        new List('brandList', {
            valueNames: ['id', 'name', 'categories', 'products', 'featured'],
            page: 10,
            pagination: true
        });
    } catch(e) {
        console.log('List.js not configured');
    }

    // Checkbox logic
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="chk_child"]').forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest('tr');
                if (row) row.classList.toggle('table-active', this.checked);
            });
            toggleRemoveBtn();
        });
    }

    document.querySelectorAll('input[name="chk_child"]').forEach(cb => {
        cb.addEventListener('change', () => {
            const row = cb.closest('tr');
            if (row) row.classList.toggle('table-active', cb.checked);
            toggleRemoveBtn();
        });
    });

    function toggleRemoveBtn() {
        const count = document.querySelectorAll('input[name="chk_child"]:checked').length;
        const removeBtn = document.getElementById('remove-actions');
        if (removeBtn) {
            removeBtn.classList.toggle('d-none', count === 0);
        }
    }

    const modal = new bootstrap.Modal('#showModal');
    const form = document.getElementById('brandForm');
    const logoPreview = document.getElementById('logo_preview');

    // Add button
    document.querySelector('.add-btn')?.addEventListener('click', () => {
        form.reset();
        document.getElementById('brand_id').value = '';
        document.getElementById('modalTitle').textContent = 'Add Brand';
        document.getElementById('submitBtn').textContent = 'Save Brand';
        if (logoPreview) {
            logoPreview.style.display = 'none';
            logoPreview.src = '';
        }
        // Reset categories select
        const categoriesSelect = document.getElementById('categories_select');
        if (categoriesSelect) {
            Array.from(categoriesSelect.options).forEach(option => {
                option.selected = false;
            });
        }
    });

    // Edit button
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            axios.get(`/brands/${id}/edit`)
                .then(res => {
                    const b = res.data;
                    document.getElementById('brand_id').value = b.id;
                    form.name.value = b.name;
                    document.getElementById('is_featured').checked = b.is_featured == 1;

                    if (b.logo && logoPreview) {
                        logoPreview.src = b.logo;
                        logoPreview.style.display = 'block';
                    } else if (logoPreview) {
                        logoPreview.style.display = 'none';
                    }

                    // Set categories
                    if (b.categories && b.categories.length > 0) {
                        const categoriesSelect = document.getElementById('categories_select');
                        Array.from(categoriesSelect.options).forEach(option => {
                            option.selected = b.categories.some(cat => cat.id == option.value);
                        });
                    }

                    document.getElementById('modalTitle').textContent = 'Edit Brand';
                    document.getElementById('submitBtn').textContent = 'Update Brand';
                    modal.show();
                })
                .catch(err => {
                    console.error('Edit error:', err);
                    Swal.fire('Error!', 'Failed to load brand data', 'error');
                });
        });
    });

    // Submit form (Create/Update)
    form.addEventListener('submit', e => {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const id = document.getElementById('brand_id').value;
        const url = id ? `/brands/${id}` : '/brands';
        const formData = new FormData(form);

        if (id) {
            formData.append('_method', 'PUT');
        }

        axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            let msg = 'An error occurred';
            if (err.response?.status === 422) {
                const errors = err.response.data.errors;
                msg = Object.values(errors).flat().join('<br>');
            } else if (err.response?.data?.message) {
                msg = err.response.data.message;
            } else if (err.response?.status === 404) {
                msg = 'Route not found. Please check your routes.';
            } else if (err.response?.status === 500) {
                msg = 'Server error. Check Laravel log for details.';
            }

            Swal.fire('Error!', msg, 'error');
            console.error('Submit error:', err);
        });
    });

    // Delete single
    let deleteId = null;
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteId = btn.dataset.id;
            new bootstrap.Modal('#deleteRecordModal').show();
        });
    });

    document.getElementById('delete-record')?.addEventListener('click', () => {
        if (!deleteId) return;

        axios.delete(`/brands/${deleteId}`)
            .then(() => {
                Swal.fire('Deleted!', 'Brand has been deleted', 'success');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(() => Swal.fire('Error', 'Cannot delete brand', 'error'));
    });

    // Multiple Delete
    window.deleteMultiple = function () {
        const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
            .map(cb => cb.value);

        if (!ids.length) {
            Swal.fire('Warning', 'Please select brands to delete', 'warning');
            return;
        }

        Swal.fire({
            title: `Delete ${ids.length} brand(s)?`,
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete them',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                Promise.all(ids.map(id => axios.delete(`/brands/${id}`)))
                    .then(() => {
                        Swal.fire('Deleted!', 'Selected brands have been deleted', 'success');
                        setTimeout(() => location.reload(), 1500);
                    })
                    .catch(() => Swal.fire('Error', 'Failed to delete some brands', 'error'));
            }
        });
    };

    // Logo preview
    const logoInput = form.querySelector('[name="logo"]');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
