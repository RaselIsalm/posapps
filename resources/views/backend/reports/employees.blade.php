@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Employee Performance Report
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        tfoot {
            background-color: #eef2f5;
            border-top: 2px solid #333;
        }

        tfoot td,
        tfoot th {
            padding: 12px 10px !important;
            font-size: 15px;
            color: #000;
        }

        thead th {
            background-color: #f8f9fa;
            font-weight: 600;
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
            Employee Performance Report
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active"> Employee Performance Report</li>
    @endcomponent
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-4 p-3 bg-light border rounded align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold text-dark">Select Branch</label>
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
                                <label class="form-label fw-bold text-dark">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ date('Y-m-01') }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold text-dark">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <button type="button" id="filterBtn" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i> Filter Report
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            {!! $dataTable->table(['class' => 'table table-bordered table-striped display', 'style' => 'width:100%'], true) !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Modal --}}
    <div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-usd me-2"></i>Commission Details: <span id="modalEmployeeName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="modalLoader" class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>

                    <div id="modalDataArea" style="display: none;">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div
                                    class="p-3 border rounded bg-light d-flex justify-content-between align-items-center shadow-sm">
                                    <div>
                                        <span class="d-block text-muted small text-uppercase fw-bold">Total Items
                                            Sold</span>
                                        <span class="fs-4 fw-bold text-dark" id="summaryQty">0 <small
                                                class="fs-6 text-muted">Pcs</small></span>
                                    </div>
                                    <div class="bg-white p-2 rounded-circle text-primary"><i class="fas fa-cubes fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded d-flex justify-content-between align-items-center shadow-sm"
                                    style="background-color: #e8f5e9; border-color: #c8e6c9 !important;">
                                    <div>
                                        <span class="d-block text-success small text-uppercase fw-bold">Total
                                            Commission</span>
                                        <span class="fs-4 fw-bold text-success" id="summaryComm">0.00</span>
                                    </div>
                                    <div class="bg-white p-2 rounded-circle text-success"><i
                                            class="fas fa-wallet fa-lg"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light sticky-top" style="z-index: 1;">
                                    <tr class="text-uppercase small text-muted">
                                        <th class="py-3">Date & Invoice</th>
                                        <th class="py-3">Product Details</th>
                                        <th class="py-3 text-center">Qty</th>
                                        <th class="py-3 text-end">Info (Tk)</th>
                                        <th class="py-3 text-end">Applied</th>
                                        <th class="py-3 text-end">Earned</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsTableBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="emptyState" class="text-center py-5 border rounded bg-light d-none">
                            <div class="text-muted mb-2"><i class="fas fa-box-open fa-3x"></i></div>
                            <p class="text-muted fw-bold">No commission records found.</p>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    <script type="text/javascript">
        $(document).ready(function() {
            var tableId = '#employee-report-table';
            var table = window.LaravelDataTables && window.LaravelDataTables['employee-report-table'];

            $(tableId).on('preXhr.dt', function(e, settings, data) {
                data.branch_id = $('#branch_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            $('#filterBtn').on('click', function() {
                table?.draw();
            });
        });

        $(document).ready(function() {
            $(document).on('click', '#employee-report-table tbody tr', function() {
                let employeeId = $(this).data('id');
                let employeeName = $(this).data('name');
                if (!employeeId) return;

                let startDate = $('input[name="start_date"]').val();
                let endDate = $('input[name="end_date"]').val();
                let branchId = $('#branch_id').val();

                $('#modalEmployeeName').text(employeeName);
                $('#modalLoader').show();
                $('#modalDataArea').hide();
                $('#detailsTableBody').empty();
                $('#emptyState').addClass('d-none');

                $('#employeeDetailsModal').modal('show');

                $.ajax({
                    url: "/reports/employee-details/" + employeeId,
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        branch_id: branchId
                    },
                    success: function(response) {
                        $('#modalLoader').hide();
                        $('#modalDataArea').fadeIn();
                        $('#summaryQty').html(response.summary.total_qty +
                            ' <small class="fs-6 text-muted">Pcs</small>');
                        $('#summaryComm').text(response.summary.total_comm);

                        if (response.data.length === 0) {
                            $('.table-responsive').hide();
                            $('#emptyState').removeClass('d-none');
                        } else {
                            $('.table-responsive').show();

                            let rows = '';
                            response.data.forEach(item => {
                                let badge = item.is_limited ?
                                    '<span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Limit</span>' :
                                    '';

                                rows += `
                            <tr>
                                <td class="align-middle">
                                    <div class="fw-bold text-dark">${item.date}</div>
                                    <small class="text-muted"><i class="fas fa-file-invoice me-1"></i>#${item.invoice}</small>
                                </td>
                                <td class="align-middle">
                                    <div class="fw-bold">${item.product_name}</div>
                                    <small class="text-muted">Code: ${item.product_code}</small>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-primary text-dark border px-3">${item.qty}</span>
                                </td>
                                <td class="text-end align-middle text-muted small">
                                    Price: ${item.selling_price}<br>
                                    Comm: ${item.prod_rate}
                                </td>
                                <td class="text-end align-middle">
                                    <span class="fw-bold text-dark">${item.applied_rate}</span>
                                    ${badge}
                                </td>
                                <td class="text-end align-middle">
                                    <span class="fw-bold text-success">${item.row_comm}</span>
                                </td>
                            </tr>
                        `;
                            });
                            $('#detailsTableBody').html(rows);
                        }
                    },
                    error: function() {
                        $('#modalLoader').hide();
                        var $alert = $('<div class="alert alert-danger floating-alert"><strong>Error!</strong> Something went wrong!</div>').appendTo('body');
                        setTimeout(function() {
                            $alert.remove();
                        }, 5000);
                    }
                });
            });
        });
    </script>
@endpush
