@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Employee Attendance
@endsection

@push('css')
    {{-- DataTables CSS --}}
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

        #employeeattendance-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        #employeeattendance-table tbody tr:hover {
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
            Employee Attendance
        @endslot
        @slot('subtitle')
            Manage and record daily attendance.
        @endslot
        <li class="breadcrumb-item active">Attendance</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Attendance Records</h4>
                    @can('create_employees_attendances')
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus me-1"></i> Add Attendance
                        </button>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        {{-- DataTable Render --}}
                        {{ $dataTable->table(['class' => 'table table-hover table-striped w-100']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-check me-2"></i><span id="attendanceModalLabel">Attendance Form</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-form-body">
                    <div class="text-center py-5">
                    </div>
                </div>
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
                    <h5 class="fw-bold mb-2">Delete Attendance?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this attendance record?</p>
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

    @include('backend.employee_attendance.partials.quick_add_employee')
@endsection

@push('js')
    {{-- Required Scripts for DataTables --}}
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

    {{-- Inject DataTable Scripts --}}
    {{ $dataTable->scripts() }}

    <script>
        // Matches the site-wide floating-alert style used for flash messages
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        let attendanceModal;

        $(document).ready(function() {
            // Menu Active
            $('#employees').addClass('active');
            $('#attendance-submenu').addClass('d-block');
            $('.attendance-menu').addClass('active');

            // Initialize Modal
            const modalEl = document.getElementById('attendanceModal');
            if (modalEl) {
                attendanceModal = new bootstrap.Modal(modalEl);
                window.attendanceModalInstance = attendanceModal;
            }
        });

        // OPEN ADD MODAL
        function openAddModal() {
            $('#attendanceModalLabel').text(@json(__('Add Attendance')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            attendanceModal.show();

            $.ajax({
                url: "{{ route('attendance.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    showAppToast(@json(__('Failed to load form.')), 'danger');
                    attendanceModal.hide();
                }
            });
        }

        // OPEN EDIT MODAL
        function editAttendance(id) {
            var url = "{{ route('attendance.edit', ':id') }}".replace(':id', id);

            $('#attendanceModalLabel').text(@json(__('Edit Attendance')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            attendanceModal.show();

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    showAppToast(@json(__('Failed to load data.')), 'danger');
                    attendanceModal.hide();
                }
            });
        }

        // HANDLE ATTENDANCE FORM SUBMISSION ONLY (not Employee quick-add)
        $(document).on('submit', '#create-attendance-form, #edit-attendance-form', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.text();

            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>');
            form.find('.form-control').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        attendanceModal.hide();
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');

                        // Show Toast
                        showFloatingAlert('success', response.message);

                        // Reload DataTable
                        $('#employeeattendance-table').DataTable().ajax.reload();
                    } else if (response.message) {
                        submitBtn.prop('disabled', false).text(originalBtnText);
                        showFloatingAlert('danger', response.message);
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(originalBtnText);
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            form.find('[name="' + key + '"]').addClass('is-invalid');
                            form.find('[name="' + key + '"]').after('<div class="invalid-feedback">' + value[0] +
                                '</div>');
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        showFloatingAlert('danger', xhr.responseJSON.message);
                    } else {
                        showAppToast(@json(__('Something went wrong!')), 'danger');
                    }
                }
            });
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
