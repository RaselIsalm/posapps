@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Purchase Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        .simple-card {
            border-radius: 6px;
            padding: 15px;
            color: #fff;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .simple-card:hover {
            transform: translateY(-3px);
        }

        .simple-card p {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 10px 0;
            font-weight: 600;
            opacity: 0.9;
        }

        .simple-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: #fff !important;
        }

        .bg-teal {
            background-color: #269085;
        }

        .bg-green {
            background-color: #4CAF50;
        }

        .bg-red {
            background-color: #EF5350;
        }

        .bg-cyan {
            background-color: #1ABC9C;
        }

        .btn-custom-excel {
            background-color: #F39C12 !important;
            color: white !important;
            border: none;
        }

        .btn-custom-pdf {
            background-color: #E74C3C !important;
            color: white !important;
            border: none;
        }

        .btn-custom-print {
            background-color: #008B8B !important;
            color: white !important;
            border: none;
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

        @media (max-width: 767.98px) {
            /* Stack Search and Buttons on Mobile */
            .dataTables_wrapper .row>.col-md-8.d-flex,
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

            /* Make Nav Tabs horizontally scrollable on mobile */
            .nav-tabs {
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                white-space: nowrap !important;
                -webkit-overflow-scrolling: touch;
                border-bottom: 1px solid #dee2e6;
            }
            
            .nav-tabs::-webkit-scrollbar {
                height: 4px;
            }
            
            .nav-tabs::-webkit-scrollbar-thumb {
                background-color: #ccc;
                border-radius: 4px;
            }
            
            .nav-tabs .nav-link {
                border: none !important;
                border-bottom: 2px solid transparent !important;
                margin-bottom: -1px;
                background: transparent !important;
            }

            .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
                border-bottom: 2px solid #269085 !important; /* Theme color */
                color: #269085 !important;
            }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Purchase Report
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Purchase Report</li>
    @endcomponent
    <div class="content-wrapper">
        <div class="container-fluid">

            <div class="card mb-4 mt-3 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Options</h5>
                </div>
                <div class="card-body">
                    <form id="filter-form">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Date Range</label>
                                <input type="text" id="daterange" class="form-control" placeholder="Select Date Range">
                                <input type="hidden" name="start_date" id="start_date">
                                <input type="hidden" name="end_date" id="end_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Supplier</label>
                                <div class="input-group rpt-field-group">
                                    <select name="supplier_id" id="supplier_id" class="form-control select2">
                                        <option value="">All Suppliers</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}
                                                ({{ $supplier->company_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_suppliers')
                                        <button type="button" class="btn btn-outline-primary btn-rpt-add"
                                            onclick="openRptSupplierModal()" title="Add New Supplier">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Branch</label>
                                <div class="input-group rpt-field-group">
                                    <select name="branch_id" id="branch_id" class="form-control select2">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_branches')
                                        <button type="button" class="btn btn-outline-primary btn-rpt-add"
                                            onclick="openRptBranchModal()" title="Add New Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100 mt-2 mt-md-0">
                                    <button type="button" id="btn-filter" class="btn btn-primary w-100" title="Filter">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-secondary w-100" title="Reset">
                                        <i class="fas fa-sync-alt"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4 g-3" id="summary-cards">
                <div class="col-md-3">
                    <div class="simple-card bg-teal">
                        <p>TOTAL PURCHASE</p>
                        <h3 id="sum-total">0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="simple-card bg-green">
                        <p>TOTAL PAID</p>
                        <h3 id="sum-paid">0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="simple-card bg-red">
                        <p>TOTAL DUE</p>
                        <h3 id="sum-due">0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="simple-card bg-cyan">
                        <p>TOTAL RETURNED</p>
                        <h3 id="sum-return">0.00</h3>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#product-tab"><i
                                    class="fas fa-box me-1"></i> Product Wise</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#supplier-tab"><i
                                    class="fas fa-users me-1"></i> Supplier Wise</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#daily-tab"><i
                                    class="fas fa-calendar-alt me-1"></i> Daily Report</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="product-tab">
                            <div class="table-responsive">
                                {!! $productTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="supplier-tab">
                            <div class="table-responsive">
                                {!! $supplierTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="daily-tab">
                            <div class="table-responsive">
                                {!! $dailyTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('backend.reports.partials.quick_add_filter', [
        'rpt' => ['supplier' => true, 'branch' => true],
    ])
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {!! $productTable->scripts() !!}
    {!! $supplierTable->scripts() !!}
    {!! $dailyTable->scripts() !!}

    <script>
        $(document).ready(function() {
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                ranges: {
                    [@json(__('Today'))]: [moment(), moment()],
                    [@json(__('Yesterday'))]: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    [@json(__('Last 7 Days'))]: [moment().subtract(6, 'days'), moment()],
                    [@json(__('This Month'))]: [moment().startOf('month'), moment().endOf('month')],
                    [@json(__('Last Month'))]: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, function(start, end) {
                $('#daterange').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $('#start_date').val(start.format('YYYY-MM-DD'));
                $('#end_date').val(end.format('YYYY-MM-DD'));
            });

            function loadSummary() {
                $.ajax({
                    url: "{{ route('reports.purchase.summary') }}",
                    method: "GET",
                    data: {
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        supplier_id: $('#supplier_id').val(),
                        branch_id: $('#branch_id').val(),
                    },
                    success: function(r) {
                        $('#sum-total').text(formatCurrency(parseFloat(r.total_purchase || 0)));
                        $('#sum-paid').text(formatCurrency(parseFloat(r.total_paid || 0)));
                        $('#sum-due').text(formatCurrency(parseFloat(r.total_due || 0)));
                        $('#sum-return').text(formatCurrency(parseFloat(r.total_return || 0)));
                    }
                });
            }
            loadSummary();

            $('#btn-filter').click(function() {
                window.LaravelDataTables?.['table-product']?.draw();
                window.LaravelDataTables?.['table-supplier']?.draw();
                window.LaravelDataTables?.['table-daily']?.draw();
                loadSummary();
            });

            $('#btn-reset').click(function() {
                $('#filter-form')[0].reset();
                $('#start_date, #end_date, #daterange').val('');
                $('#supplier_id').val('').trigger('change');
                $('#branch_id').val('').trigger('change');

                window.LaravelDataTables?.['table-product']?.draw();
                window.LaravelDataTables?.['table-supplier']?.draw();
                window.LaravelDataTables?.['table-daily']?.draw();
                loadSummary();
            });

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                $.fn.dataTable.tables({
                    visible: true,
                    api: true
                }).columns.adjust();
            });
        });
    </script>
@endpush
