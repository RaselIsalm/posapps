@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Activity Logs
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
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

        .filter-label {
            font-weight: 600;
            font-size: 13px;
            color: #080808ff;
            margin-bottom: 5px;
        }

        /* Processing Loader Fix */

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

        .dataTables_wrapper .dt-buttons {
            position: relative;
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
            System Activity Logs
        @endslot
        @slot('subtitle')
            Track and monitor user actions across the system.
        @endslot
        <li class="breadcrumb-item active">Activity Logs</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">

        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                style="border-bottom: 1px solid #f0f0f0; cursor: pointer;" data-bs-toggle="collapse"
                data-bs-target="#advancedFilter">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filter</h6>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="collapse show" id="advancedFilter">
                <div class="card-body bg-light">
                    <form id="filter-form" class="row">

                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label text-uppercase">Log Type</label>
                            <select name="log_name" id="log_name" class="form-control form-select">
                                <option value="">All Modules</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label text-uppercase">Section</label>
                            <select name="subject_type" id="subject_type" class="form-control form-select">
                                <option value="">All Sections</option>
                                @foreach ($subjectTypes as $type)
                                    <option value="{{ $type }}">{{ class_basename($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label text-uppercase">User</label>
                            <select name="user_id" id="user_id" class="form-control form-select">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label text-uppercase">Date Range</label>
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                <input type="date" name="start_date" id="start_date" class="form-control w-100">
                                <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                <input type="date" name="end_date" id="end_date" class="form-control w-100">
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end mt-3 mt-md-0">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" id="btn-filter" class="btn btn-primary w-100 text-white">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <button type="button" id="reset-filter" class="btn btn-dark w-100 text-white">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-4 border-0">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list text-primary me-1"></i> All User Activities</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-hover w-100']) !!}
                </div>
            </div>
        </div>

        </div>
    </div>

    <div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="activityLogModalLabel"><i class="fas fa-history me-2"></i>Log Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal_body_content">
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading details...</p>
                    </div>
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

    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {

            $(document).on('preXhr.dt', function(e, settings, data) {
                data.log_name = $('#log_name').val();
                data.subject_type = $('#subject_type').val();
                data.user_id = $('#user_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                window.LaravelDataTables?.["activity-log-table"]?.ajax.reload();
            });

            $('#reset-filter').on('click', function() {
                $('#filter-form')[0].reset();
                window.LaravelDataTables?.["activity-log-table"]?.ajax.reload();
            });

            $(document).on('click', '#activity-log-table tbody tr', function(e) {
                e.preventDefault();

                let url = $(this).find('.view-log-btn').data('url');
                if (!url) return;

                $('#modal_body_content').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading details...</p></div>'
                    );
                $('#activityLogModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#modal_body_content').html(response);
                    },
                    error: function() {
                        $('#modal_body_content').html(
                            '<div class="alert alert-danger">Something went wrong. Please try again later.</div>'
                            );
                    }
                });
            });

            $('#activity-log-table tbody').css('cursor', 'pointer');

        });
    </script>
@endpush
