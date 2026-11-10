@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Summary Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    

    <style>
        :root {
            --bg: #f3f4f6;
        }

        body {
            background-color: var(--bg);
            color: #111827;
        }

        .card {
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            border-radius: 12px;
            background: #fff;
            margin-bottom: 24px;
        }

        .filter-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 22px;
        }

        .summary-sidebar td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .dt-buttons .btn-excel {
            background-color: #f59e0b !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 6px 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            margin-right: 5px;
        }

        .dt-buttons .btn-pdf {
            background-color: #ef4444 !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 6px 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            margin-right: 5px;
        }

        .dt-buttons .btn-print {
            background-color: #06b6d4 !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 6px 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
        }

        .dt-buttons button:hover {
            opacity: 0.9;
        }

        .main-table-wrapper table.dataTable thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            padding: 12px 14px !important;
            border-bottom: 1px solid #e5e7eb !important;
            white-space: nowrap;
        }

        .main-table-wrapper table.dataTable tbody td {
            padding: 12px 14px !important;
            border-bottom: 1px solid #f3f4f6 !important;
            font-size: 13px;
            font-variant-numeric: tabular-nums;
        }

        .main-table-wrapper table.dataTable tfoot th {
            background: #fff;
            border-top: 2px solid #e5e7eb !important;
            padding: 12px 14px !important;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            white-space: nowrap;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: #fff !important;
            border: 1px solid var(--primary-color) !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            border: none !important;
        }

        @media (max-width: 767.98px) {
            .mobile-scroll-btn-group {
                overflow-x: auto;
                white-space: nowrap;
            }
            .mobile-full-width {
                width: 100% !important;
            }
            .dt-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                margin-bottom: 15px;
            }
            .dt-buttons button {
                margin-right: 0 !important;
                flex-grow: 1;
            }
            .dataTables_filter {
                margin-top: 10px;
                text-align: left !important;
            }
            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 5px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid py-4">

            <div class="filter-wrapper">
                <form action="{{ route('reports.summary') }}" method="GET" id="filter-form">
                    <input type="hidden" name="range" id="filter_range" value="{{ request('range') }}">

                    <div class="row align-items-end g-3">

                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Branch Selection</label>
                            <div class="input-group rpt-field-group">
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}
                                        </option>
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

                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Date Range</label>
                            <input type="text" id="daterange" name="daterange" class="form-control"
                                value="{{ request('daterange') }}" autocomplete="off">
                            <input type="hidden" name="start_date" id="start_date"
                                value="{{ $startDate->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-lg-6 col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Quick Filters</label>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <div class="btn-group flex-grow-1 border rounded p-1 bg-light mobile-scroll-btn-group" role="group">
                                    <button type="button"
                                        class="btn btn-sm {{ request('range') == 'today' ? 'btn-primary' : 'btn-dark' }} flex-fill js-quick"
                                        data-range="today">Today</button>
                                    <button type="button"
                                        class="btn btn-sm {{ request('range') == '7days' ? 'btn-primary' : 'btn-dark' }} flex-fill js-quick"
                                        data-range="7days">7 Days</button>
                                    <button type="button"
                                        class="btn btn-sm {{ request('range') == 'month' ? 'btn-primary' : 'btn-dark' }} flex-fill js-quick"
                                        data-range="month">This Month</button>
                                    <button type="button"
                                        class="btn btn-sm {{ request('range') == 'year' ? 'btn-primary' : 'btn-dark' }} flex-fill js-quick"
                                        data-range="year">This Year</button>
                                </div>
                                <button type="submit" class="btn btn-primary px-4 text-nowrap mobile-full-width"
                                    onclick="$('#filter_range').val('')">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold text-dark m-0">Summary Report</h4>
                    <div class="text-muted small mt-1"><i class="far fa-clock"></i> Period:
                        <b>{{ $startDate->format('d M, Y') }}</b> — <b>{{ $endDate->format('d M, Y') }}</b></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="card p-0 overflow-hidden h-100">
                        <div class="p-3 border-bottom bg-light">
                            <h6 class="mb-0 fw-bold text-uppercase small text-muted">Overview</h6>
                        </div>
                        <table class="summary-sidebar w-100">
                            <tbody>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-shopping-cart text-primary opacity-50"></i>
                                        Total Sales</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_sales'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-percent text-muted opacity-50"></i> Sales Tax
                                    </td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_tax'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-undo text-danger opacity-50"></i> Sales
                                        Return</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_sales_return'] ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-truck text-info opacity-50"></i> Total
                                        Purchase</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_purchase'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-truck-loading text-info opacity-50"></i>
                                        Total P. Return</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_purchase_return'] ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-receipt text-warning opacity-50"></i> Total
                                        Expenses</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_expense'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label"><i class="fas fa-user-friends text-secondary opacity-50"></i>
                                        Salary Pay</td>
                                    <td class="summary-value">{{ \App\Helpers\CurrencyHelper::format($totals['total_salary'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="summary-label fw-bold">Net Profit</td>
                                    <td class="summary-value fw-bold">{{ \App\Helpers\CurrencyHelper::format($totals['total_profit'] ?? 0, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card h-100 border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark">Daily Breakdown</h6>
                        </div>
                        <div class="table-responsive p-3 main-table-wrapper">
                            {{-- {!! $dataTable->table(['class' => 'table table-hover w-100 mb-0 border-0']) !!} --}}
                            <table id="summary-table" class="table table-hover w-100 mb-0 border-0">
                                <thead>
                                    <tr>
                                        <th>DATE</th>
                                        <th class="text-end">SALES</th>
                                        <th class="text-end">SALES TAX</th>
                                        <th class="text-end">RETURN</th>
                                        <th class="text-end">PURCHASE</th>
                                        <th class="text-end">PURCHASE RETURN</th>
                                        <th class="text-end">EXPENSE</th>
                                        <th class="text-end">SALARY</th>
                                        <th class="text-end">PROFIT</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>

                                <tfoot>
                                    <tr>
                                        <th>Total Summary</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                        <th class="text-end">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
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
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    {!! $dataTable->scripts() !!}

    <script>
        (function() {
            const $daterange = $('#daterange');
            const $start = $('#start_date');
            const $end = $('#end_date');
            const initialStart = moment($start.val(), 'YYYY-MM-DD');
            const initialEnd = moment($end.val(), 'YYYY-MM-DD');

            function applyRange(start, end) {
                $daterange.val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $start.val(start.format('YYYY-MM-DD'));
                $end.val(end.format('YYYY-MM-DD'));
            }

            $daterange.daterangepicker({
                autoUpdateInput: false,
                startDate: initialStart.isValid() ? initialStart : moment().startOf('month'),
                endDate: initialEnd.isValid() ? initialEnd : moment().endOf('month'),
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            }, function(start, end) {
                applyRange(start, end);
                $('#filter_range').val('');
            });

            if (!$daterange.val()) applyRange(initialStart, initialEnd);

            $(document).on('click', '.js-quick', function() {
                const type = $(this).data('range');

                $('#filter_range').val(type);

                $('.js-quick').removeClass('btn-primary').addClass('btn-dark');
                $(this).removeClass('btn-dark').addClass('btn-primary');

                let start, end;
                if (type === 'today') {
                    start = moment();
                    end = moment();
                } else if (type === '7days') {
                    start = moment().subtract(6, 'days');
                    end = moment();
                } else if (type === 'month') {
                    start = moment().startOf('month');
                    end = moment().endOf('month');
                } else if (type === 'year') {
                    start = moment().startOf('year');
                    end = moment().endOf('year');
                }

                applyRange(start, end);
                $('#filter-form').trigger('submit');
            });
        })();
    </script>
@endpush
