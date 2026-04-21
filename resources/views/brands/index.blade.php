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

            <!-- Add Brand Button -->
            <div class="row mb-3">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Brand
                    </button>
                </div>
            </div>

            <!-- Brands Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Logo</th>
                                            <th>Featured</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($brands as $brand)
                                        <tr>
                                            <td>{{ $brand->id }}</td>
                                            <td>{{ $brand->name }}</td>
                                            <td>
                                                @if($brand->logo)
                                                    <img src="{{ asset('storage/'.$brand->logo) }}" width="50" height="50" class="rounded">
                                                @else
                                                    No Logo
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $brand->is_featured ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $brand->is_featured ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-btn" data-id="{{ $brand->id }}">Edit</button>
                                                <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $brand->id }}">Delete</button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No brands found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $brands->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="showModal" tabindex="-1">
    <div class="modal-dialog">
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
                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="brand_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" id="brand_logo" accept="image/*">
                        <div id="logoPreview" class="mt-2"></div>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="bi bi-trash text-danger display-4"></i>
                <h4 class="mt-4">Delete Brand?</h4>
                <p class="text-muted">This action cannot be undone.</p>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set CSRF token for axios
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }

    let deleteId = null;

    // Add Brand Button
    const addBtn = document.querySelector('.add-btn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            document.getElementById('brandForm').reset();
            document.getElementById('brand_id').value = '';
            document.getElementById('modalTitle').textContent = 'Add Brand';
            document.getElementById('submitBtn').textContent = 'Save Brand';
            document.getElementById('logoPreview').innerHTML = '';
        });
    }

    // Edit Buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;

            axios.get(`/brands/${id}/edit`)
                .then(response => {
                    const brand = response.data;
                    document.getElementById('brand_id').value = brand.id;
                    document.getElementById('brand_name').value = brand.name;
                    document.getElementById('is_featured').checked = brand.is_featured == 1;

                    if (brand.logo) {
                        document.getElementById('logoPreview').innerHTML = `<img src="${brand.logo}" width="100" class="rounded">`;
                    }

                    document.getElementById('modalTitle').textContent = 'Edit Brand';
                    document.getElementById('submitBtn').textContent = 'Update Brand';

                    const modal = new bootstrap.Modal(document.getElementById('showModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Failed to load brand data', 'error');
                });
        });
    });

    // Delete Buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.id;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

    // Confirm Delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!deleteId) return;

        axios.delete(`/brands/${deleteId}`)
            .then(() => {
                Swal.fire('Deleted!', 'Brand has been deleted.', 'success');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete brand', 'error');
                console.error(error);
            });
    });

    // Form Submit (Create/Update)
    const form = document.getElementById('brandForm');
    if (form) {
        form.addEventListener('submit', function(e) {
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
                Swal.fire('Success!', response.data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                let errorMessage = 'An error occurred';
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }

                Swal.fire('Error!', errorMessage, 'error');
                console.error('Error:', error);
            });
        });
    }

    // Logo Preview
    const logoInput = document.getElementById('brand_logo');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').innerHTML = `<img src="${e.target.result}" width="100" class="rounded">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
