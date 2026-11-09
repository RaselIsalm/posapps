@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Transfers
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .filter-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px 25px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
            border: 1px solid #f0f0f0;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .form-select,
        .form-control {
            border-color: #e5e5e5;
            font-size: 14px;
            padding: 0.6rem 0.75rem;
            color: #555;
            border-radius: 6px;
        }

        .btn-filter {
            background-color: #248888;
            color: white;
            border: none;
            font-weight: 600;
            padding: 0.6rem;
            border-radius: 6px;
        }

        .btn-filter:hover {
            background-color: #1e7070;
            color: white;
        }

        .btn-reset {
            background-color: #212529;
            color: white;
            border: none;
            font-weight: 600;
            padding: 0.6rem;
            border-radius: 6px;
        }

        .btn-reset:hover {
            background-color: #000;
            color: white;
        }

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

        #transfer-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        
        #transfer-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .page-body {
            min-height: auto !important;
        }

        @media (max-width: 767.98px) {
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
            Product Transfer List
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active"> Product Transfer List</li>
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
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">From Branch</label>
                            <select id="from_branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach (\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">To Branch</label>
                            <select id="to_branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach (\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">Status</label>
                            <select id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="sent">Sent</option>
                                <option value="request">Request</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Date Range</label>
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                <input type="date" id="start_date" class="form-control w-100">
                                <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                <input type="date" id="end_date" class="form-control w-100">
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end mt-3 mt-md-0">
                            <div class="d-flex gap-2 w-100">
                                <button type="button" class="btn btn-primary w-100 text-white" id="btn-filter" style="background-color: #248888; border-color: #248888;">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <button type="button" class="btn btn-dark w-100 text-white" id="btn-reset">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 gap-md-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>All Transfers</h6>
                    @if (Auth::user()->can('create_transfer'))
                        <a href="{{ route('transfers.create') }}" class="btn btn-primary shadow-sm text-white"
                            style="background-color: #248888; border-color: #248888;">
                            <i class="fas fa-plus-circle me-1"></i> Add Transfer
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-hover w-100', 'id' => 'transfer-table']) }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transferDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" id="details_modal_content"></div>
        </div>
    </div>

    {{-- Status Confirmation Modal --}}
    <div class="modal fade" id="statusConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(var(--primary-rgb, 48, 142, 135), 0.1);">
                        <i class="fas fa-check-circle fs-3" style="color: var(--primary-color, #308e87);"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="statusConfirmTitle">Confirm Action?</h5>
                    <p class="text-muted mb-4" id="statusConfirmText">Are you sure?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary text-white px-4" id="confirmStatusBtn">
                            <i class="fas fa-check-circle me-1"></i> Confirm
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
                    <h5 class="fw-bold mb-2">Delete Transfer?</h5>
                    <p class="text-muted mb-4">If status is 'Sent', stock will be restored to Sender Branch. Are you sure?</p>
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    {{ $dataTable->scripts() }}

    <script>
        // Override default pagination language and set a spinner for processing
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                processing: '',
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {


            const table = window.LaravelDataTables && window.LaravelDataTables['transfer-table'];

            $('#btn-filter').click(function() {
                table?.draw();
            });

            $('#btn-reset').click(function() {
                $('#from_branch_id').val('');
                $('#to_branch_id').val('');
                $('#status').val('');
                table?.draw();
            });

            $('#transfer-table tbody').on('click', 'tr', function(e) {
                if ($(e.target).closest('a, button, input, .btn, .dropdown-toggle').length) {
                    return;
                }

                let rowData = table.row(this).data();

                if (rowData && rowData.id) {
                    viewTransferDetails(rowData.id);
                }
            });

            let deleteUrl = '';
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                deleteUrl = "{{ route('transfers.destroy', ':id') }}".replace(':id', id);
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                deleteModal.show();
            });

            $('#confirmDeleteBtn').on('click', function() {
                let $btn = $(this);
                let originalContent = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...').prop('disabled', true);

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#deleteConfirmModal').modal('hide');
                        $btn.html(originalContent).prop('disabled', false);
                        if (res.success) {
                            if(typeof showFloatingAlert === 'function') showFloatingAlert('success', res.message);
                            window.LaravelDataTables?.['transfer-table']?.ajax.reload();
                        } else {
                            if(typeof showFloatingAlert === 'function') showFloatingAlert('danger', res.message);
                        }
                    },
                    error: function(xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        $btn.html(originalContent).prop('disabled', false);
                        if(typeof showFloatingAlert === 'function') showFloatingAlert('danger', @json(__('Failed to delete.')));
                    }
                });
            });

            let statusUrl = '';
            let newStatusVal = '';
            
            $(document).on('click', '.status-btn', function() {
                let id = $(this).data('id');
                newStatusVal = $(this).data('status');
                statusUrl = "{{ route('transfers.update_status', ':id') }}".replace(':id', id);

                let confirmMsg = '';
                let confirmTitle = '';
                if (newStatusVal === 'sent') {
                    confirmTitle = @json(__('Approve Transfer?'));
                    confirmMsg = @json(__('Stock will be deducted from Sender Branch.'));
                } else if (newStatusVal === 'completed') {
                    confirmTitle = @json(__('Receive Stock?'));
                    confirmMsg = @json(__('Stock will be added to Receiver Branch.'));
                }

                $('#statusConfirmTitle').text(confirmTitle);
                $('#statusConfirmText').text(confirmMsg);

                var statusModal = new bootstrap.Modal(document.getElementById('statusConfirmModal'));
                statusModal.show();
            });

            $('#confirmStatusBtn').on('click', function() {
                let $btn = $(this);
                let originalContent = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + @json(__('Updating...'))).prop('disabled', true);

                $.ajax({
                    url: statusUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: newStatusVal
                    },
                    success: function(res) {
                        $('#statusConfirmModal').modal('hide');
                        $btn.html(originalContent).prop('disabled', false);
                        if (res.success) {
                            if(typeof showFloatingAlert === 'function') showFloatingAlert('success', res.message);
                            window.LaravelDataTables?.['transfer-table']?.ajax.reload();
                        } else {
                            if(typeof showFloatingAlert === 'function') showFloatingAlert('danger', res.message);
                        }
                    },
                    error: function(xhr) {
                        $('#statusConfirmModal').modal('hide');
                        $btn.html(originalContent).prop('disabled', false);
                        if(typeof showFloatingAlert === 'function') showFloatingAlert('danger', @json(__('Error:')) + ' ' + xhr.responseText);
                    }
                });
            });
        });

        window.viewTransferDetails = function(id) {
            openGlobalInvoiceModal("{{ route('transfers.show', ':id') }}".replace(':id', id));
        }

        function showFloatingAlert(type, message) {
            $('.floating-alert').remove(); // Clear existing
            var label = type === 'success' ? @json(__('Success!')) : type === 'warning' ? @json(__('Warning!')) : @json(__('Error!'));
            var bsClass = type === 'error' ? 'danger' : type;
            var $alert = $('<div class="alert alert-' + bsClass + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }
    </script>
@endpush
