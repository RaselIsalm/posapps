@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Sale Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <style>
        .filter-card {
            border: 1px solid #e0e0e0;
            box-shadow: none;
            border-radius: 6px;
            background: #fff;
            margin-bottom: 20px;
        }

        .filter-header {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .filter-body {
            padding: 20px;
        }

        .custom-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.5px;
        }

        .form-control,
        .form-select {
            border-color: #e0e0e0;
            box-shadow: none;
            font-size: 13px;
            height: 38px;
        }

        .input-group-text {
            background-color: #fff;
            border-color: #e0e0e0;
            font-size: 13px;
            color: #666;
        }

        .btn-filter {
            background-color: #2c9688;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 20px;
            border: none;
            height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-filter:hover {
            background-color: #247a6f;
            color: #fff;
        }

        .btn-reset {
            background-color: #333;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 20px;
            border: none;
            height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-reset:hover {
            background-color: #000;
            color: #fff;
        }

        .summary-card {
            border: none;
            border-radius: 8px;
            padding: 15px;
            color: white;
            text-align: center;
        }

        .summary-title {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
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

        .dataTables_wrapper .dt-buttons {
            /* removed relative positioning hack to match sale list page */
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
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
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Sales Report
        @endslot
        @slot('subtitle')
            Daily Sales Summary
        @endslot
        <li class="breadcrumb-item active">Sales Report</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">

            <div class="card filter-card">
                <div class="filter-header">
                    <i class="fas fa-filter me-2 text-muted"></i> Advanced Filter
                </div>
                <div class="filter-body">
                    <form action="{{ route('reports.sales') }}" method="GET" id="filterForm">
                        <div class="row g-3">

                            <div class="col-md-5">
                                <span class="custom-label">Date Range</span>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ $fromDate ?? '' }}">
                                    <span class="input-group-text border-start-0 border-end-0">to</span>
                                    <input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <span class="custom-label">Branch</span>
                                <div class="input-group rpt-field-group">
                                    <select name="branch_id" id="branch_id" class="form-select">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
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

                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-filter w-100 m-0">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('reports.sales') }}" class="btn btn-reset w-100 m-0">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="summary-card" style="background-color: #2c9688;">
                        <div class="summary-title">Total Sales</div>
                        <div class="summary-value">{{ \App\Helpers\CurrencyHelper::format($summary->total_sales ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card" style="background-color: #4caf50;">
                        <div class="summary-title">Total Paid</div>
                        <div class="summary-value">{{ \App\Helpers\CurrencyHelper::format($summary->total_paid ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card" style="background-color: #e7515a;">
                        <div class="summary-title">Total Due</div>
                        <div class="summary-value">{{ \App\Helpers\CurrencyHelper::format($summary->total_due ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card" style="background-color: #1abc9c;">
                        <div class="summary-title">Invoice Count</div>
                        <div class="summary-value">{{ $summary->total_invoices ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="card filter-card">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover table-striped w-100']) }}
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
@endpush
