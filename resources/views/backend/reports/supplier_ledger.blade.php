@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Supplier Ledger Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }
    </style>
@endpush
@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Supplier Ledger Report
        @endslot
        @slot('subtitle')
            Create and manage your supplier relationships effectively.
        @endslot
        <li class="breadcrumb-item active">Supplier Ledger Report</li>
    @endcomponent
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form id="ledger-filter-form">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label>Select Supplier <span class="text-danger">*</span></label>
                            <div class="input-group rpt-field-group">
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->phone }})
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
                            <label>Select Branch</label>
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
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" id="filterBtn" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-bordered table-striped'], true) !!}
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
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {
            var table = window.LaravelDataTables && window.LaravelDataTables['supplier-ledger-table'];

            $('#supplier-ledger-table').on('preXhr.dt', function(e, settings, data) {
                data.supplier_id = $('#supplier_id').val();
                data.branch_id = $('#branch_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            $('#filterBtn').on('click', function() {
                var supplier_id = $('#supplier_id').val();

                if (!supplier_id) {
                    var $alert = $('<div class="alert alert-danger floating-alert"><strong>Error!</strong> Please select a supplier first.</div>').appendTo('body');
                    setTimeout(function() {
                        $alert.remove();
                    }, 5000);
                    return;
                }

                table?.draw() || $('#supplier-ledger-table').DataTable().ajax.reload();
            });
        });
    </script>
@endpush
