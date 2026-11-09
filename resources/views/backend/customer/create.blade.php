@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create Customer
@endsection

@push('css')
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')Create Customer @endslot
    @slot('subtitle')Create and manage your customer relationships effectively. @endslot
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">Create</li>
@endcomponent

<div class="row layout-top-spacing">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12 d-flex justify-content-between">
                        <h4>Create Customer</h4>
                        <a href="{{ route('customers.index') }}" class="btn btn-primary btn-sm mr-2 float-right">
                            <i class="fa-solid fa-list"></i> Customer List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="note">Note</label>
                                <input type="text" class="form-control" id="note" name="note" placeholder="Note">
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="customer_group_id">Customer Group</label>
                                <div class="d-flex align-items-center gap-1">
                                    <select class="form-control" id="customer_group_id" name="customer_group_id" style="flex: 1;">
                                        <option disabled selected value="">Select Group</option>
                                        @foreach ($customer_groups as $customer_group)
                                            <option value="{{ $customer_group->id }}">{{ $customer_group->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickCustomerGroupModal()" title="Add New Customer Group" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="previous_due">Previous Due</label>
                                <input type="number" class="form-control" id="previous_due" name="previous_due" placeholder="Previous Due">
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="status">Is Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input switch-size-lg" type="checkbox" checked role="switch" id="status" name="status" style="width: 50px; height: 25px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

    {{-- Quick Add Customer Group Modal --}}
    <div class="modal fade" id="quickCustomerGroupModal" tabindex="-1" aria-labelledby="quickCustomerGroupModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="quickCustomerGroupModalTitle"><i class="fas fa-users me-2"></i>Add Customer Group</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickCustomerGroupForm">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_cg_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_cg_name" name="name" placeholder="Customer Group Name" required>
                                <div class="invalid-feedback" id="quick_cg_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_cg_prefix_code" class="form-label">Prefix Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_cg_prefix_code" name="prefix_code" placeholder="Prefix Code" required>
                                <div class="invalid-feedback" id="quick_cg_prefix_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_cg_discount" class="form-label">Discount</label>
                                <input type="number" step="0.01" class="form-control" id="quick_cg_discount" name="discount" value="0" placeholder="Discount" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_cg_type" class="form-label">Discount Type</label>
                                <select class="form-control" id="quick_cg_type" name="type">
                                    <option value="percentage" selected>Percentage</option>
                                    <option value="fixed">Fixed ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_cg_description" class="form-label">Description</label>
                                <textarea class="form-control" id="quick_cg_description" name="description" placeholder="Description" rows="2"></textarea>
                            </div>
                            <div class="col-md-3 col-12 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="form-check-label" for="quick_cg_status">Is Active</label>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input switch-size-lg" type="checkbox" checked role="switch" id="quick_cg_status" name="status" style="width: 50px; height: 25px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-md" id="quickCgSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickCgSpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#peoples').addClass('active');
    $('#peoples-submenu').addClass('d-block');
    $('.customer-menu').addClass('active');

    // Quick Add Customer Group Modal
    let quickCgModalInstance;
    $(document).ready(function() {
        const cgModalEl = document.getElementById('quickCustomerGroupModal');
        if (cgModalEl) {
            quickCgModalInstance = new bootstrap.Modal(cgModalEl);
        }
    });

    function openQuickCustomerGroupModal() {
        $('#quickCustomerGroupForm')[0].reset();
        $('#quick_cg_name, #quick_cg_prefix_code').removeClass('is-invalid');
        $('#quick_cg_error, #quick_cg_prefix_error').text('');
        $('#quick_cg_status').prop('checked', true);
        quickCgModalInstance.show();
        setTimeout(() => $('#quick_cg_name').focus(), 300);
    }

    $('#quickCustomerGroupForm').on('submit', function(e) {
        e.preventDefault();

        const name = $('#quick_cg_name').val().trim();
        const prefix = $('#quick_cg_prefix_code').val().trim();
        let isValid = true;

        if (!name) {
            $('#quick_cg_name').addClass('is-invalid');
            $('#quick_cg_error').text(@json(__('Name is required.')));
            isValid = false;
        }
        if (!prefix) {
            $('#quick_cg_prefix_code').addClass('is-invalid');
            $('#quick_cg_prefix_error').text(@json(__('Prefix code is required.')));
            isValid = false;
        }

        if(!isValid) return;

        $('#quickCgSaveBtn').prop('disabled', true);
        $('#quickCgSpinner').removeClass('d-none');
        $('#quick_cg_name, #quick_cg_prefix_code').removeClass('is-invalid');

        var formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');

        if (!$('#quick_cg_status').is(':checked')) {
            formData.delete('status');
        }

        $.ajax({
            url: "{{ route('customer-groups.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    const newOption = new Option(response.customer_group.name, response.customer_group.id, true, true);
                    $('#customer_group_id').append(newOption);

                    quickCgModalInstance.hide();

                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('success', @json(__('Customer Group ":name" created!')).replace(':name', response.customer_group.name));
                    } else if(typeof alert !== 'undefined') {
                        alert(@json(__('Customer Group ":name" created!')).replace(':name', response.customer_group.name));
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#quick_cg_name').addClass('is-invalid');
                        $('#quick_cg_error').text(errors.name[0]);
                    }
                    if (errors.prefix_code) {
                        $('#quick_cg_prefix_code').addClass('is-invalid');
                        $('#quick_cg_prefix_error').text(errors.prefix_code[0]);
                    }
                } else {
                    $('#quick_cg_name').addClass('is-invalid');
                    $('#quick_cg_error').text(@json(__('Failed to create. Please try again.')));
                }
            },
            complete: function() {
                $('#quickCgSaveBtn').prop('disabled', false);
                $('#quickCgSpinner').addClass('d-none');
            },
        });
    });

    function showFloatingAlert(type, message) {
        var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
        var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
        $('body').append($alert);
        setTimeout(function() {
            $alert.remove();
        }, 5000);
    }
</script>
@endpush