@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Department
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
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

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        /* Update selector according to table ID */
        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .table tbody tr:hover {
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
            Department
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active"> Department</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12 d-flex justify-content-between align-items-center">
                            <h4>All Department</h4>
                            @can('create_departments')
                                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                                    <i class="fas fa-plus me-1"></i> Add Department
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover table-striped w-100']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="departmentModal" tabindex="-1" role="dialog" aria-labelledby="departmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-sitemap me-2"></i><span id="modalTitle">Create New Department</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="departmentForm" action="{{ route('departments.store') }}" method="POST">
                    @csrf
                    <div id="method_field"></div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Department Name" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Department Code</label>
                                <div class="input-group">
                                    <input type="text" name="code" id="code" class="form-control" readonly>
                                    <button type="button" id="generateCodeBtn" class="btn btn-primary">
                                        Generate
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Department Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="Department Description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="form-check-label" for="is_active">Is Active</label>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input switch-size-lg" type="checkbox" role="switch"
                                                id="is_active" name="is_active" checked style="width: 50px; height: 25px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btn-save">Save</button>
                    </div>
                </form>
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
                    <h5 class="fw-bold mb-2">Delete Department?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this department?</p>
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
        $('#department').addClass('active');
        $('#department-submenu').addClass('d-block');
        $('.department-menu').addClass('active');

        function openCreateModal() {
            $('#departmentForm').trigger("reset");
            $('#modalTitle').text(@json(__('Create New Department')));
            $('#btn-save').text(@json(__('Create')));

            $('#departmentForm').attr('action', "{{ route('departments.store') }}");
            $('#method_field').html('');

            $('#is_active').prop('checked', true);

            $('#departmentModal').modal('show');
        }

        function editDepartment(element) {
            let id = $(element).data('id');
            let name = $(element).data('name');
            let code = $(element).data('code');
            let description = $(element).data('description');
            let status = $(element).data('status');

            $('#modalTitle').text(@json(__('Edit Department')));
            $('#btn-save').text(@json(__('Update')));

            let updateUrl = "{{ route('departments.update', ':id') }}";
            updateUrl = updateUrl.replace(':id', id);
            $('#departmentForm').attr('action', updateUrl);

            $('#method_field').html('<input type="hidden" name="_method" value="PUT">');

            $('#name').val(name);
            $('#code').val(code);
            $('#description').val(description);

            if (status == 1) {
                $('#is_active').prop('checked', true);
            } else {
                $('#is_active').prop('checked', false);
            }

            $('#departmentModal').modal('show');
        }

        // Generate Code
        document.getElementById('generateCodeBtn').addEventListener('click', function() {
            let randomCode = "DEP-" + Math.floor(10000 + Math.random() * 90000);
            document.getElementById('code').value = randomCode;
        });

        // MODAL CONFIRMATION FOR DELETE
        let formToSubmit = null;

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            formToSubmit = $(this).closest('form');
            $('#deleteConfirmModal').modal('show');
        });

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (formToSubmit) {
                $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);
                formToSubmit.submit();
            }
        });
    </script>
@endpush
