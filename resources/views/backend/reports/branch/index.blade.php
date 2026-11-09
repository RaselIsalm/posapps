@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Branch Performance Report
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
        }

        .bg-teal {
            background-color: #269085;
        }

        .bg-cyan {
            background-color: #1ABC9C;
        }

        .bg-red {
            background-color: #EF5350;
        }

        .bg-orange {
            background-color: #F39C12;
        }

        .bg-green {
            background-color: #4CAF50;
        }

        .nav-tabs .nav-link.active {
            color: #269085;
            border-top: 3px solid #269085;
        }

        .btn-orange {
            background-color: #F39C12 !important;
            color: white !important;
            border-color: #F39C12 !important;
        }

        .btn-red {
            background-color: #E74C3C !important;
            color: white !important;
            border-color: #E74C3C !important;
        }

        .btn-teal {
            background-color: #008080 !important;
            color: white !important;
            border-color: #008080 !important;
        }

        .dataTables_wrapper .row:first-child {
            align-items: center;
            margin-bottom: 15px;
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

        .dt-buttons {
            display: flex;
            gap: 5px;
            margin-left: 10px;
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
                margin-left: 0 !important;
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
            Branch Performance Report
        @endslot
        @slot('subtitle')
            Analyze sales, purchases, and expenses for each branch.
        @endslot
        <li class="breadcrumb-item active">Branch Report</li>
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
                                <input type="hidden" name="start_date" id="start_date" value="{{ date('Y-m-01') }}">
                                <input type="hidden" name="end_date" id="end_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Select Branch</label>
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
                            <div class="col-md-2">
                                <button type="button" id="btn-filter" class="btn btn-primary w-100"><i
                                        class="fas fa-search"></i> Filter</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-reset" class="btn btn-secondary w-100"><i
                                        class="fas fa-sync-alt"></i> Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4 g-3" id="summary-cards">
                <div class="col-md-2 col-sm-6">
                    <div class="simple-card bg-teal">
                        <p>Total Sales</p>
                        <h3 id="card_sales">0.00</h3>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="simple-card bg-cyan">
                        <p>Total Purchase</p>
                        <h3 id="card_purchase">0.00</h3>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="simple-card bg-red">
                        <p>Total Expense</p>
                        <h3 id="card_expense">0.00</h3>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="simple-card bg-orange">
                        <p>Total Return</p>
                        <h3 id="card_return">0.00</h3>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="simple-card bg-green">
                        <p>Net Profit (Approx)</p>
                        <h3 id="card_profit">0.00</h3>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sales-tab">Sales
                                Report</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#purchase-tab">Purchase
                                Report</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#expense-tab">Expense Report</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#return-tab">Return Report</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="sales-tab">
                            <div class="table-responsive">
                                {!! $salesTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="purchase-tab">
                            <div class="table-responsive">
                                {!! $purchaseTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="expense-tab">
                            <div class="table-responsive">
                                {!! $expenseTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="return-tab">
                            <div class="table-responsive">
                                {!! $returnTable->table(['class' => 'table table-bordered table-striped w-100']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.reports.partials.quick_add_filter', [
        'rpt' => ['branch' => true],
    ])
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {!! $salesTable->scripts() !!}
    {!! $purchaseTable->scripts() !!}
    {!! $expenseTable->scripts() !!}
    {!! $returnTable->scripts() !!}

    <script>
        $(document).ready(function() {
            // Date Range Picker
            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function cb(start, end) {
                $('#daterange').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $('#start_date').val(start.format('YYYY-MM-DD'));
                $('#end_date').val(end.format('YYYY-MM-DD'));
            }

            $('#daterange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    [@json(__('Today'))]: [moment(), moment()],
                    [@json(__('This Month'))]: [moment().startOf('month'), moment().endOf('month')],
                    [@json(__('Last Month'))]: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
            cb(start, end);

            // Global Filter Logic
            $(document).on('preXhr.dt', function(e, settings, data) {
                data.branch_id = $('#branch_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            // Load Summary Cards
            function loadSummary() {
                $.ajax({
                    url: "{{ route('reports.branch.summary') }}",
                    data: {
                        branch_id: $('#branch_id').val(),
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                    },
                    success: function(res) {
                        $('#card_sales').text(res.total_sales);
                        $('#card_purchase').text(res.total_purchase);
                        $('#card_expense').text(res.total_expense);
                        $('#card_return').text(res.total_return);
                        $('#card_profit').text(res.net_profit);
                    },
                    error: function(err) {
                        var $alert = $('<div class="alert alert-danger floating-alert"><strong>Error!</strong> Something went wrong!</div>').appendTo('body');
                        setTimeout(function() {
                            $alert.remove();
                        }, 5000);
                    }
                });
            }

            // Action Buttons
            $('#btn-filter').click(function() {
                loadSummary();
                window.LaravelDataTables?.['sales-table']?.draw();
                window.LaravelDataTables?.['purchase-table']?.draw();
                window.LaravelDataTables?.['expense-table']?.draw();
                window.LaravelDataTables?.['return-table']?.draw();
            });

            $('#btn-reset').click(function() {
                $('#filter-form')[0].reset();
                cb(moment().startOf('month'), moment().endOf('month'));
                $('#branch_id').val('').trigger('change');
                $('#btn-filter').click();
            });

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                $.fn.dataTable.tables({
                    visible: true,
                    api: true
                }).columns.adjust();
            });

            loadSummary();
        });
    </script>
@endpush
