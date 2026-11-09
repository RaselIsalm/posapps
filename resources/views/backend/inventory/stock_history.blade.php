@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Stock History
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <style>
        body {
            background-color: #f5f7fb;
        }

        .card-custom {
            background: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(8, 21, 66, .05);
            padding: 20px;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #5b626b;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 1px solid #ebedf2;
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            color: #3b3f5c;
            font-size: 14px;
            border-bottom: 1px solid #ebedf2;
        }

        #stock-history-table tbody tr {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        #stock-history-table tbody tr:hover {
            background-color: #f8f9fa !important;
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

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {
            .dataTables_wrapper .row > .col-md-6.d-flex {
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
                top: 0 !important;
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
            Stock Movement History
        @endslot
        @slot('subtitle')
            Track all inventory ins and outs for auditing
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Stock History</li>
    @endcomponent

    <div class="container-fluid py-4">
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                style="border-bottom: 1px solid #f0f0f0; cursor: pointer;" data-bs-toggle="collapse"
                data-bs-target="#advancedFilter">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filter</h6>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="collapse show" id="advancedFilter">
                <div class="card-body bg-light">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Branch</label>
                            <select id="branch_filter" class="form-select form-select-sm shadow-sm" style="height: 38px; font-size: 14px;">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Type</label>
                            <select id="type_filter" class="form-select form-select-sm shadow-sm" style="height: 38px; font-size: 14px;">
                                <option value="">All Types</option>
                                <option value="sale">Sale</option>
                                <option value="purchase">Purchase</option>
                                <option value="transfer_sent">Transfer Sent</option>
                                <option value="transfer_received">Transfer Received</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Start Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm shadow-sm" style="height: 38px; font-size: 14px;">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">End Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm shadow-sm" style="height: 38px; font-size: 14px;">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button id="filter_btn" class="btn btn-primary btn-sm w-100 shadow-sm" style="height: 38px;">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <button id="reset_btn" class="btn btn-dark border btn-sm w-100 shadow-sm" style="height: 38px;">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i>Movement Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 border-bottom pb-2 mb-2">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Product</small>
                                    <span class="fw-bold text-dark fs-6" id="h_product">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Variant</small>
                                    <span class="fw-bold text-dark" id="h_variant">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Branch</small>
                                    <span class="fw-bold text-dark" id="h_branch">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Quantity</small>
                                    <span class="fw-bold fs-5" id="h_qty">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Type</small>
                                    <span id="h_type">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Reference No</small>
                                    <span class="fw-bold text-primary" id="h_reference">...</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Date & Time</small>
                                    <span class="fw-bold text-dark" id="h_date">...</span>
                                </div>
                                <div class="col-12 border-top pt-2 mt-2">
                                    <small class="text-muted d-block">Note / Reason</small>
                                    <p class="text-muted small mb-0 fst-italic" id="h_note">...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
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

    {{ $dataTable->scripts() }}

    <script>
        function showCustomToast(message, type = 'success') {
            window.showAppToast(message, type);
        }

        $(document).ready(function() {
            const tableId = '#stock-history-table';

            $(tableId).on('preXhr.dt', function(e, settings, data) {
                data.branch_id = $('#branch_filter').val();
                data.type = $('#type_filter').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            $('#filter_btn').on('click', function() {
                $(tableId).DataTable().ajax.reload();
            });

            $('#reset_btn').on('click', function() {
                $('#branch_filter, #type_filter, #start_date, #end_date').val('');
                $(tableId).DataTable().ajax.reload();
            });

            $('body').on('click', tableId + ' tbody tr', function(e) {
                if ($(e.target).closest('button, a, .dropdown-menu, input, i').length > 0) {
                    return;
                }

                var id = $(this).attr('data-id');
                if (!id) return;

                // Reset fields
                $('#h_product, #h_variant, #h_branch, #h_qty, #h_type, #h_reference, #h_date, #h_note').text(@json(__('Loading...')));
                
                $('#historyDetailsModal').modal('show');

                $.ajax({
                    url: "{{ route('stock-history.show', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#h_product').text(data.product_name + ' (' + data.product_code + ')');
                        $('#h_variant').text(data.variant);
                        $('#h_branch').text(data.branch);
                        $('#h_qty').text(data.qty).removeClass('text-success text-danger').addClass(data.qty_color);
                        $('#h_type').html(data.type);
                        $('#h_reference').text(data.reference);
                        $('#h_date').text(data.date);
                        $('#h_note').text(data.note);
                    },
                    error: function() {
                        showCustomToast(@json(__('Error loading history details')), 'error');
                        $('#historyDetailsModal').modal('hide');
                    }
                });
            });
        });
    </script>
@endpush
