@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Purchase List
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
        .table thead th {
            background-color: #f8f9fa;
            color: #5b6e88;
            font-weight: 600;
            font-size: 13px;
            padding: 12px;
            border-bottom: 1px solid #eef1f6;
            text-transform: uppercase;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 14px;
            padding: 12px;
            border-bottom: 1px solid #f2f4f8;
            color: #333;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 6px 12px;
            height: 38px;
            margin-left: 0;
            width: 200px;
        }

        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-bottom: -10px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {

            /* Stack Search and Buttons on Mobile */
            .dataTables_wrapper .row>.col-md-6.d-flex {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
                margin-top: 10px;
            }

            div.dataTables_wrapper div.dataTables_filter {
                text-align: left;
                margin: 0;
                width: 100%;
            }

            div.dataTables_wrapper div.dataTables_filter label {
                display: block;
                width: 100%;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                width: 100%;
                margin-left: 0;
                display: block;
            }

            .dt-buttons {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                gap: 5px;
                margin-bottom: 10px;
            }

            .dt-buttons .btn {
                flex: 1;
                margin: 0 !important;
                padding: 8px 4px;
                font-size: 13px;
            }

            .table thead th,
            .table tbody td {
                white-space: nowrap;
                /* Prevent squashing columns */
            }

            div.dataTables_wrapper div.dataTables_length {
                text-align: left;
            }

            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: center !important;
                flex-wrap: wrap;
                margin-top: 15px;
            }

            div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-link {
                white-space: nowrap !important;
                padding: 8px 12px;
            }

            .input-group {
                flex-wrap: nowrap;
            }
        }

        /* Hide theme-injected pseudo-element arrows globally */
        .dataTables_paginate .pagination .page-item::before,
        .dataTables_paginate .pagination .page-item::after,
        .dataTables_paginate .pagination .page-link::before,
        .dataTables_paginate .pagination .page-link::after {
            display: none !important;
            content: none !important;
        }

        /* In-modal payment errors (visible while Add Payment modal is open) */
        #paymentModal .pur-pay-form-error {
            display: none;
            border: 1px solid #f5c2c7;
            background: #f8d7da;
            color: #842029;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 0.875rem;
            line-height: 1.45;
        }

        #paymentModal .pur-pay-form-error.is-visible {
            display: block;
        }

        #paymentModal .pur-pay-form-error-title {
            font-weight: 700;
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
    @slot('title')
    Purchase list
    @endslot
    @slot('subtitle')
    Manage and configure your system's fundamental preferences and parameters.
    @endslot
    <li class="breadcrumb-item active"> Purchase list</li>
    @endcomponent

    <div class="container-fluid" style="min-height: calc(100vh - 120px);">
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                style="border-bottom: 1px solid #f0f0f0; cursor: pointer;" data-bs-toggle="collapse"
                data-bs-target="#advancedFilter">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filter</h6>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="collapse show" id="advancedFilter">
                <div class="card-body bg-light">
                    <form id="filterForm">
                        <div class="row">
                            {{-- Branch --}}
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="filter-label">Branch</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-code-branch"></i></span>
                                    <select id="branch_id" class="form-select">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="filter-label">Date Range</label>
                                <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                    <input type="date" id="start_date" class="form-control w-100">
                                    <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                    <input type="date" id="end_date" class="form-control w-100">
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="filter-label">Status</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-list-ul"></i></span>
                                    <select class="form-select" id="filter_status">
                                        <option value="">All Status</option>
                                        <option value="received">Received</option>
                                        <option value="pending">Pending</option>
                                        <option value="ordered">Ordered</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Filter Buttons --}}
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" id="filter" class="btn btn-primary w-100 text-white">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <button type="button" id="reset" class="btn btn-dark w-100 text-white">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-list me-2"></i> Purchase List</h5>
                @can('create_purchases')
                    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Purchase
                    </a>
                @endcan
            </div>
            <div class="card-body p-4 pt-0">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table w-100', 'width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="purchase_id" id="modal_purchase_id">
                    <div class="modal-body">
                        <div class="pur-pay-form-error" id="purPayFormError" role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="any" class="form-control" name="amount" id="modal_amount" required>
                            <small class="text-danger">Max Due: <span id="display_due">0.00</span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="pur_due_payment_method">Payment Account <span class="text-danger">*</span></label>
                            <div class="input-group flex-nowrap pur-field-group">
                                <select name="payment_method" id="pur_due_payment_method" class="form-control" required>
                                    @forelse ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @empty
                                        <option value="" disabled selected>No payment accounts found</option>
                                    @endforelse
                                </select>
                                @can('create_accounts')
                                    <button class="btn btn-outline-primary btn-pur-add" type="button"
                                        onclick="openPurAccountModal()" title="Add Payment Account"
                                        aria-label="Add Payment Account">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note (Optional)</label>
                            <textarea class="form-control" name="note" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg p-0 overflow-hidden">
                <div class="modal-body p-0" id="modal_body_content">
                    <div class="text-center py-5">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Purchase?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this purchase? This will reduce stock!</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.purchase.partials.quick_add_account', [
        'paymentMethodSelectId' => 'pur_due_payment_method',
        'parentModalId' => 'paymentModal',
    ])
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            // Function to show toast alert
            function showFloatingAlert(type, message) {
                var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
                var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
                $('body').append($alert);
                setTimeout(function() {
                    $alert.remove();
                }, 5000);
            }

            const tableId = '#purchases-table';

            $(tableId).on('preXhr.dt', function (e, settings, data) {
                data.branch_id = $('#branch_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.status = $('#filter_status').val();
            });

            // Filter Button
            $('#filter').on('click', function () {
                $(tableId).DataTable().ajax.reload();
            });

            // Reset Button
            $('#reset').on('click', function () {
                $('#branch_id').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#filter_status').val('');
                $(tableId).DataTable().ajax.reload();
            });

            function showPurPayFormError(message) {
                var box = document.getElementById('purPayFormError');
                if (!box) return;
                var text = String(message || @json(__('Payment processing failed. Please try again.')));
                box.innerHTML = '<div class="pur-pay-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Payment failed</div><div></div>';
                box.querySelector('div:last-child').textContent = text;
                box.classList.add('is-visible');
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }

            function clearPurPayFormError() {
                var box = document.getElementById('purPayFormError');
                if (!box) return;
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }

            window.openPaymentModal = function (id, dueAmount) {
                clearPurPayFormError();
                $('#modal_purchase_id').val(id);
                $('#modal_amount').val(dueAmount);
                $('#modal_amount').attr('max', dueAmount);
                $('#display_due').text(dueAmount);
                $('#paymentModal').modal('show');
            }

            $('#paymentForm').on('submit', function (e) {
                e.preventDefault();
                clearPurPayFormError();
                var formData = $(this).serialize();
                let submitBtn = $(this).find('button[type="submit"]');
                let originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('purchases.add_payment') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        submitBtn.prop('disabled', false).html(originalBtnText);

                        if (response && response.success === false) {
                            showPurPayFormError(response.message || @json(__('Payment processing failed. Please try again.')));
                            return;
                        }

                        $('#paymentModal').modal('hide');
                        $('#paymentForm')[0].reset();
                        clearPurPayFormError();
                        $(tableId).DataTable().ajax.reload();
                        showFloatingAlert('success', (response && response.message) || @json(__('Payment Added Successfully!')));
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);

                        let errorMessage = @json(__('Payment processing failed. Please try again.'));

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errs = xhr.responseJSON.errors;
                            var firstKey = Object.keys(errs)[0];
                            if (firstKey && errs[firstKey] && errs[firstKey][0]) {
                                errorMessage = errs[firstKey][0];
                            }
                        } else if (xhr.responseText) {
                            try {
                                let res = JSON.parse(xhr.responseText);
                                if (res.message) {
                                    errorMessage = res.message;
                                }
                            } catch (e) { }
                        }

                        // Keep modal open — show error inside modal (not behind backdrop toast)
                        showPurPayFormError(errorMessage);
                    }
                });
            });

            let deleteUrl = '';
            
            window.openDeleteModal = function (url) {
                deleteUrl = url;
                $('#deleteConfirmModal').modal('show');
            };

            $('#confirmDeleteBtn').on('click', function () {
                let btn = $(this);
                let originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

                $.ajax({
                    url: deleteUrl,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.prop('disabled', false).html(originalText);
                        
                        if (response.success) {
                            showFloatingAlert('success', response.message || @json(__('Purchase deleted successfully!')));
                            $(tableId).DataTable().ajax.reload(null, false);
                        } else {
                            showFloatingAlert('error', response.message || @json(__('Error deleting purchase!')));
                        }
                    },
                    error: function (xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.prop('disabled', false).html(originalText);
                        showFloatingAlert('error', @json(__('Something went wrong! Failed to delete purchase.')));
                    }
                });
            });

            $(document).on('click', '#purchases-table tbody tr', function (e) {
                if ($(e.target).closest('button, a, .dropdown-menu, input, .btn, form').length > 0) {
                    return;
                }

                let id = $(this).attr('data-id') || $(this).data('id');
                if (!id) {
                    return;
                }

                let url = "{{ route('purchases.show', ':id') }}".replace(':id', id);
                if (typeof openGlobalInvoiceModal === 'function') {
                    openGlobalInvoiceModal(url);
                } else {
                    // Fallback to local details modal
                    $('#detailsModal').modal('show');
                    $('#modal_body_content').html(
                        '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading Details...</p></div>'
                    );
                    $.get(url, function (response) {
                        $('#modal_body_content').html(response);
                    }).fail(function () {
                        $('#modal_body_content').html(
                            '<div class="text-center py-5 text-danger">Failed to load data.</div>'
                        );
                    });
                }
            });

        });
    </script>
    {{ $dataTable->scripts() }}
@endpush