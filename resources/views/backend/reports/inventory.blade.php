@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Inventory Valuation Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
            border-top: 2px solid #dee2e6;
        }

        tfoot td {
            padding: 15px 10px !important;
            font-size: 16px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        .dataTables_wrapper .dt-buttons {
            position: relative;
            top: -16px;
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }
    </style>
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Inventory Valuation Report
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active"> Inventory Valuation Report</li>
    @endcomponent
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row mb-4 p-3 bg-light border rounded">

                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold text-dark">Branch</label>
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

                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold text-dark">Category</label>
                                <div class="input-group rpt-field-group">
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_categories')
                                        <button type="button" class="btn btn-outline-primary btn-rpt-add"
                                            onclick="openRptCategoryModal()" title="Add New Category">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold text-dark">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold text-dark">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>

                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <div class="w-100">
                                    <button type="button" id="filterBtn" class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive product-table">
                            {!! $dataTable->table(['class' => 'table table-bordered table-striped display', 'style' => 'width:100%'], true) !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.reports.partials.quick_add_filter', [
        'rpt' => ['branch' => true, 'category' => true],
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

    <script type="text/javascript">
        $(document).ready(function() {

            var table = window.LaravelDataTables && window.LaravelDataTables['inventory-report-table'];

            $('#inventory-report-table').on('preXhr.dt', function(e, settings, data) {
                data.branch_id = $('#branch_id').val();
                data.category_id = $('#category_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            $('#filterBtn').on('click', function() {
                table?.draw();
            });
        });
    </script>
@endpush
