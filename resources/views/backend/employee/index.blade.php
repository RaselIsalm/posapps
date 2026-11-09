@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Employee
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

        #employee-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        #employee-table tbody tr:hover {
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

        /* Quick-add uses pause/resume (hide parent), not stacked modals */
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Employee
        @endslot
        @slot('subtitle')
            Manage employee records.
        @endslot
        <li class="breadcrumb-item active">Employee</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">All Employees</h4>
                    @can('create_employees')
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus me-1"></i> Add New Employee
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
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i><span id="employeeModalLabel">Employee Form</span></h5>
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
                    <h5 class="fw-bold mb-2">Delete Employee?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this employee?</p>
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

    {{-- Quick Add Department Modal --}}
    <div class="modal fade" id="quickDepartmentModal" tabindex="-1" aria-labelledby="quickDepartmentModalTitle"
        aria-hidden="true" data-bs-backdrop="static" style="z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="quickDepartmentModalTitle">
                        <i class="fas fa-sitemap me-2"></i>Add Department
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickDepartmentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="quick_dept_name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_dept_name" name="name"
                                placeholder="Department Name" required>
                            <div class="invalid-feedback" id="quick_dept_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="quick_dept_code" class="form-label">Department Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="quick_dept_code" name="code"
                                    placeholder="DEP-12345" required>
                                <button type="button" class="btn btn-primary" id="quickGenerateDeptCodeBtn">Generate</button>
                            </div>
                            <div class="invalid-feedback d-block" id="quick_dept_code_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="quick_dept_description" class="form-label">Description</label>
                            <textarea class="form-control" id="quick_dept_description" name="description" rows="2"
                                placeholder="Department Description"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="quick_dept_is_active"
                                name="is_active" checked style="width: 40px; height: 20px;">
                            <label class="form-check-label ms-2" for="quick_dept_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickDepartmentSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickDepartmentSpinner"
                                role="status"></span>
                            Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

        let employeeModal;
        let quickDepartmentModal;
        window.__empQuickPausedParent = false;

        function empClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        // Quotation/Attendance style: hide Employee modal while nested quick-add is open
        window.empOpenOverEmployeeModal = function (showQuickFn) {
            const parentEl = document.getElementById('employeeModal');
            let opened = false;
            const openQuick = function () {
                if (opened) return;
                opened = true;
                if (typeof showQuickFn === 'function') showQuickFn();
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                (typeof $ !== 'undefined' && $(parentEl).is(':visible'))
            );
            if (!parentVisible) {
                openQuick();
                return;
            }
            window.__empQuickPausedParent = true;
            const onHidden = function () {
                parentEl.removeEventListener('hidden.bs.modal', onHidden);
                openQuick();
            };
            parentEl.addEventListener('hidden.bs.modal', onHidden);
            if (employeeModal) employeeModal.hide();
            else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                (bootstrap.Modal.getInstance(parentEl) || bootstrap.Modal.getOrCreateInstance(parentEl)).hide();
            } else {
                $('#employeeModal').modal('hide');
            }
            setTimeout(function () {
                parentEl.removeEventListener('hidden.bs.modal', onHidden);
                openQuick();
            }, 450);
        };

        window.empResumeEmployeeModal = function () {
            setTimeout(function () {
                if (!window.__empQuickPausedParent) return;
                const nestOpen = document.querySelector(
                    '#empAddBranchModal.show, #empAddRoleModal.show, #quickDepartmentModal.show, #nxNestManagerModal.show, #nxNestAccountModal.show'
                );
                if (nestOpen) return;
                window.__empQuickPausedParent = false;
                empClearStrayBackdrops();
                if (employeeModal) employeeModal.show();
                else $('#employeeModal').modal('show');
            }, 350);
        };

        window.empBindQuickResume = function (quickModalId) {
            const el = document.getElementById(quickModalId);
            if (!el || el.dataset.empResumeBound === '1') return;
            el.dataset.empResumeBound = '1';
            el.addEventListener('hidden.bs.modal', function () {
                window.empResumeEmployeeModal();
            });
        };

        $(document).ready(function() {
            // Menu Active Logic
            $('#employees').addClass('active');
            $('#employees-submenu').addClass('d-block');
            $('.employees-menu').addClass('active');

            // Initialize Modal
            const modalEl = document.getElementById('employeeModal');
            if (modalEl) {
                employeeModal = new bootstrap.Modal(modalEl);
                window.employeeModalInstance = employeeModal;
            }

            const deptModalEl = document.getElementById('quickDepartmentModal');
            if (deptModalEl) {
                if (deptModalEl.parentElement !== document.body) {
                    document.body.appendChild(deptModalEl);
                }
                quickDepartmentModal = new bootstrap.Modal(deptModalEl);
                window.empBindQuickResume('quickDepartmentModal');
            }
        });

        window.openQuickDepartmentModal = function() {
            $('#quickDepartmentForm')[0].reset();
            $('#quick_dept_name, #quick_dept_code').removeClass('is-invalid');
            $('#quick_dept_name_error, #quick_dept_code_error').text('');
            $('#quick_dept_is_active').prop('checked', true);
            $('#quick_dept_code').val('DEP-' + Math.floor(10000 + Math.random() * 90000));
            window.empOpenOverEmployeeModal(function () {
                if (quickDepartmentModal) quickDepartmentModal.show();
                else $('#quickDepartmentModal').modal('show');
                setTimeout(function() { $('#quick_dept_name').focus(); }, 300);
            });
        };

        $(document).on('click', '#quickGenerateDeptCodeBtn', function() {
            $('#quick_dept_code').val('DEP-' + Math.floor(10000 + Math.random() * 90000));
        });

        $(document).on('submit', '#quickDepartmentForm', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const name = $('#quick_dept_name').val().trim();
            const code = $('#quick_dept_code').val().trim();
            let isValid = true;

            $('#quick_dept_name, #quick_dept_code').removeClass('is-invalid');
            $('#quick_dept_name_error, #quick_dept_code_error').text('');

            if (!name) {
                $('#quick_dept_name').addClass('is-invalid');
                $('#quick_dept_name_error').text(@json(__('Name is required.')));
                isValid = false;
            }
            if (!code) {
                $('#quick_dept_code').addClass('is-invalid');
                $('#quick_dept_code_error').text(@json(__('Code is required.')));
                isValid = false;
            }
            if (!isValid) return;

            $('#quickDepartmentSaveBtn').prop('disabled', true);
            $('#quickDepartmentSpinner').removeClass('d-none');

            var formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');
            if (!$('#quick_dept_is_active').is(':checked')) {
                formData.delete('is_active');
            }

            $.ajax({
                url: "{{ route('departments.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if (response.success && response.department) {
                        const dept = response.department;
                        const $select = $('#employeeModal #department_id');
                        if ($select.find('option[value="' + dept.id + '"]').length === 0) {
                            $select.append(new Option(dept.name, dept.id, true, true));
                        } else {
                            $select.val(dept.id);
                        }
                        if (quickDepartmentModal) quickDepartmentModal.hide();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            if (key === 'name') {
                                $('#quick_dept_name').addClass('is-invalid');
                                $('#quick_dept_name_error').text(value[0]);
                            } else if (key === 'code') {
                                $('#quick_dept_code').addClass('is-invalid');
                                $('#quick_dept_code_error').text(value[0]);
                            }
                        });
                    } else {
                        showFloatingAlert('danger', @json(__('Failed to create department.')));
                    }
                },
                complete: function() {
                    $('#quickDepartmentSaveBtn').prop('disabled', false);
                    $('#quickDepartmentSpinner').addClass('d-none');
                }
            });
        });

        // OPEN ADD MODAL
        function openAddModal() {
            $('#empAddBranchModal, #empAddRoleModal').remove();
            $('.modal-backdrop.emp-quick-backdrop').remove();
            $('#employeeModalLabel').text(@json(__('Add New Employee')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            employeeModal.show();

            $.ajax({
                url: "{{ route('employees.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    showAppToast(@json(__('Failed to load form.')), 'danger');
                    employeeModal.hide();
                }
            });
        }

        // OPEN EDIT MODAL
        function editEmployee(id) {
            var url = "{{ route('employees.edit', ':id') }}".replace(':id', id);

            $('#empAddBranchModal, #empAddRoleModal').remove();
            $('.modal-backdrop.emp-quick-backdrop').remove();
            $('#employeeModalLabel').text(@json(__('Edit Employee')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            employeeModal.show();

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    showAppToast(@json(__('Failed to load data.')), 'danger');
                    employeeModal.hide();
                }
            });
        }

        var empPhotoHint = {!! json_encode(__('Please choose a JPG, PNG, WEBP, GIF, AVIF, BMP or HEIC image up to 5 MB.')) !!};

        $(document).on('change', '.emp-photo-input', function () {
            var input = this;
            var box = input.closest('.emp-photo');
            if (!box) return;
            var file = input.files && input.files[0];
            var nameEl = box.querySelector('.pos-file-name');
            var img = box.querySelector('.emp-photo-img');
            var fallback = box.querySelector('.emp-photo-fallback');
            var error = box.querySelector('.emp-photo-error');
            var empty = nameEl ? nameEl.getAttribute('data-empty') : '';
            if (error) {
                error.classList.add('d-none');
                error.textContent = '';
            }
            if (!file) {
                if (nameEl) nameEl.textContent = empty;
                return;
            }
            var okType = /\.(jpe?g|png|gif|webp|avif|bmp|heic|heif)$/i.test(file.name);
            if (!okType || file.size > 5 * 1024 * 1024) {
                input.value = '';
                if (nameEl) nameEl.textContent = empty;
                if (error) {
                    error.textContent = empPhotoHint;
                    error.classList.remove('d-none');
                }
                return;
            }
            if (nameEl) nameEl.textContent = file.name;
            var remove = box.querySelector('.emp-photo-remove');
            if (remove) remove.checked = false;
            if (img && file.type && file.type.indexOf('image/') === 0 && file.type !== 'image/heic' && file.type !== 'image/heif') {
                var reader = new FileReader();
                reader.onload = function (ev) {
                    img.src = ev.target.result;
                    img.classList.remove('d-none');
                    if (fallback) fallback.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        $(document).on('change', '.emp-photo-remove', function () {
            if (!this.checked) return;
            var box = this.closest('.emp-photo');
            if (!box) return;
            var input = box.querySelector('.emp-photo-input');
            var nameEl = box.querySelector('.pos-file-name');
            var img = box.querySelector('.emp-photo-img');
            var fallback = box.querySelector('.emp-photo-fallback');
            if (input) input.value = '';
            if (nameEl) nameEl.textContent = nameEl.getAttribute('data-empty') || '';
            if (img) {
                img.classList.add('d-none');
                img.removeAttribute('src');
            }
            if (fallback) fallback.classList.remove('d-none');
        });

        // HANDLE EMPLOYEE FORM SUBMISSION ONLY
        $(document).on('submit', '#create-employee-form, #edit-employee-form', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.text();

            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>');
            form.find('.form-control, .form-select').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            form.find('.emp-photo-error').addClass('d-none').text('');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        employeeModal.hide();
                        $('#empAddBranchModal, #empAddRoleModal').remove();
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');

                        // Show Toast
                        showFloatingAlert('success', response.message);

                        // Reload DataTable
                        $('#employee-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(originalBtnText);
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors || {};
                        if (Object.keys(errors).length) {
                            $.each(errors, function(key, value) {
                                if (key === 'image') {
                                    form.find('.emp-photo-error').removeClass('d-none').text(value[0]);
                                    return;
                                }
                                var $field = form.find('[name="' + key + '"]');
                                $field.addClass('is-invalid');
                                if ($field.next('.invalid-feedback').length === 0) {
                                    $field.after('<div class="invalid-feedback">' + value[0] + '</div>');
                                }
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            showAppToast(xhr.responseJSON.message, 'danger');
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        showAppToast(xhr.responseJSON.message, 'danger');
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
