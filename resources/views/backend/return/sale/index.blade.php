@extends('backend.layout.main')
@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Sale Return
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

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

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 13px;
            color: #555;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        .dropdown-toggle::after {
            display: none;
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

        #sale-return-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #sale-return-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

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
        }

        /* Hide theme-injected pseudo-element arrows globally */
        .dataTables_paginate .pagination .page-item::before,
        .dataTables_paginate .pagination .page-item::after,
        .dataTables_paginate .pagination .page-link::before,
        .dataTables_paginate .pagination .page-link::after {
            display: none !important;
            content: none !important;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
    @slot('title')
    Sale Return List
    @endslot
    @slot('subtitle')
    Manage and configure your system's fundamental preferences and parameters.
    @endslot
    <li class="breadcrumb-item active"> Sale Return List</li>
    @endcomponent
    <div class="container-fluid">
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                style="border-bottom: 1px solid #f0f0f0; cursor: pointer;" data-bs-toggle="collapse"
                data-bs-target="#advancedFilter">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filter</h6>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="collapse show" id="advancedFilter">
                <div class="card-body bg-light">
                    <div class="row">
                        {{-- Branch --}}
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Branch</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-code-branch"></i></span>
                                <select id="branch_id" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach (\App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="filter-label">Date Range</label>
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                <input type="date" id="start_date" class="form-control w-100">
                                <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                <input type="date" id="end_date" class="form-control w-100">
                            </div>
                        </div>

                        {{-- Filter Buttons --}}
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="button" id="btn-filter" class="btn btn-primary w-100 text-white">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <button type="button" id="btn-reset" class="btn btn-dark w-100 text-white">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                @if (Auth::user()->can('create_sale_return'))
                                    <button type="button" class="btn btn-primary w-100 text-white" data-bs-toggle="modal" data-bs-target="#createReturnModal">
                                        <i class="fas fa-plus-circle me-1"></i> Add Return
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table w-100', 'width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE RETURN MODAL --}}
    <div class="modal fade" id="createReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i>Create Sale Return</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="searchSection">
                        <div class="text-center py-4">
                            <label class="form-label fw-bold mb-3">Enter Sale Invoice Number</label>
                            <div class="input-group mb-3 w-75 mx-auto">
                                <input type="text" id="invoiceSearchInput"
                                    class="form-control form-control-lg text-center" placeholder="INV-2026..." autofocus>
                                <button class="btn btn-primary" type="button" id="btnSearchInvoice">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <small class="text-muted">Enter the invoice number to load products.</small>
                            <div id="searchError" class="text-danger mt-2 fw-bold" style="display:none;"></div>
                        </div>
                    </div>
                    <div id="returnFormContainer" style="display:none;">
                        <div class="text-center py-5">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Modal Container --}}
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg p-0 overflow-hidden">
                <div class="modal-body p-0" id="invoice_modal_content">
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Return</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit_modal_content"></div>
            </div>
        </div>
    </div>

    {{-- Approve Confirmation Modal --}}
    <div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(var(--primary-rgb, 48, 142, 135), 0.1);">
                        <i class="fas fa-check-circle fs-3" style="color: var(--primary-color, #308e87);"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Approve Sale Return?</h5>
                    <p class="text-muted mb-4">Are you sure you want to approve this return? This will update the Stock & Wallet!</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary text-white px-4" id="confirmApproveBtn">
                            <i class="fas fa-check-circle me-1"></i> Approve
                        </button>
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
                    <h5 class="fw-bold mb-2">Delete Sale Return?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this sale return? This will REVERT the stock changes!</p>
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
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        // Override default pagination language to remove arrows
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                }
            }
        });
    </script>
    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables && window.LaravelDataTables['sale-return-table'];

            // Filter Logic
            $('#btn-filter').click(function() {
                table?.draw();
            });

            // Reset Logic
            $('#btn-reset').click(function() {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#branch_id').val('');
                table?.draw();
            });

            // Pass Filter Values to Controller
            table?.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date   = $('#end_date').val();
                data.branch_id  = $('#branch_id').val();
            });

            // --- Row Click Event ---
            $('#sale-return-table tbody').on('click', 'tr', function(e) {
                if ($(e.target).closest('button, a, .dropdown, .btn-group, input, i').length) {
                    return;
                }

                let id = $(this).attr('data-id');

                if (id) {
                    viewReturnDetails(id);
                }
            });

            // Action Menu
            $(document).on('click', '.view-details-btn', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                viewReturnDetails(id);
            });

            // --- CREATE RETURN LOGIC ---
            $('#btnSearchInvoice').on('click', function() {
                let invoiceNo = $('#invoiceSearchInput').val();
                let errorDiv = $('#searchError');
                let searchSection = $('#searchSection');
                let formContainer = $('#returnFormContainer');

                if (!invoiceNo) {
                    errorDiv.text(@json(__('Please enter an invoice number!'))).show();
                    return;
                }
                errorDiv.hide();
                searchSection.hide();
                formContainer.show().html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Searching Invoice...</p></div>'
                );

                $.ajax({
                    url: "{{ route('sale-return.create') }}",
                    type: "GET",
                    data: {
                        sale_reference: invoiceNo
                    },
                    success: function(response) {
                        if (response.status === 'error') {
                            formContainer.hide();
                            searchSection.show();
                            errorDiv.text(response.message).show();
                        } else {
                            formContainer.html(response);
                        }
                    },
                    error: function(xhr) {
                        formContainer.hide();
                        searchSection.show();
                        errorDiv.text(@json(__('Invoice not found or Server Error!'))).show();
                    }
                });
            });

            // Allow searching invoice by pressing 'Enter' key
            $('#invoiceSearchInput').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    $('#btnSearchInvoice').click();
                }
            });

            $('#createReturnModal').on('hidden.bs.modal', function() {
                $('#searchSection').show();
                $('#returnFormContainer').hide().html('');
                $('#invoiceSearchInput').val('');
                $('#searchError').hide();
            });

            // --- DETAILS & OTHER ACTIONS ---
            window.viewReturnDetails = function(id) {
                openGlobalInvoiceModal("{{ route('sale-return.show', ':id') }}".replace(':id', id));
            }

            window.editReturn = function(id) {
                let url = "{{ route('sale-return.edit', ':id') }}".replace(':id', id);
                $('#editReturnModal').modal('show');
                $('#edit_modal_content').html(
                    '<div class="text-center py-5"><div class="spinner-border text-warning"></div></div>');
                $.get(url, function(response) {
                    $('#edit_modal_content').html(response);
                });
            }

            // Delete Logic
            let deleteUrl = '';
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                deleteUrl = "{{ route('sale-return.destroy', ':id') }}".replace(':id', id);
                $('#deleteConfirmModal').modal('show');
            });

            $(document).on('click', '#confirmDeleteBtn', function() {
                if (!deleteUrl) return;

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);

                        if (res.success) {
                            table?.ajax.reload(null, false);
                            if(typeof showFloatingAlert === 'function') {
                                showFloatingAlert('success', res.message || @json(__('Sale return deleted successfully.')));
                            }
                        } else {
                            if(typeof showFloatingAlert === 'function') {
                                showFloatingAlert('danger', @json(__('Error:')) + ' ' + res.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);

                        let msg = @json(__('Something went wrong! Please try again.'));
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        
                        if(typeof showFloatingAlert === 'function') {
                            showFloatingAlert('danger', msg);
                        }
                    }
                });
            });

            // Approve Logic
            let approveUrl = '';
            $(document).on('click', '.approve-btn', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                approveUrl = "{{ route('sale-return.approve', ':id') }}".replace(':id', id);
                $('#approveConfirmModal').modal('show');
            });

            $(document).on('click', '#confirmApproveBtn', function() {
                if (!approveUrl) return;

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Approving...').prop('disabled', true);

                $.ajax({
                    url: approveUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#approveConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);

                        if (res.success) {
                            table?.ajax.reload(null, false);
                            if(typeof showFloatingAlert === 'function') {
                                showFloatingAlert('success', res.message);
                            }
                        } else {
                            if(typeof showFloatingAlert === 'function') {
                                showFloatingAlert('danger', @json(__('Error:')) + ' ' + res.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#approveConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);

                        let msg = @json(__('Something went wrong! Please try again.'));
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if(typeof showFloatingAlert === 'function') {
                            showFloatingAlert('danger', msg);
                        }
                    }
                });
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
