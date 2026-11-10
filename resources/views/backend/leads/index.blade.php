@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Leads
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

        #leads-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        #leads-table tbody tr:hover {
            background-color: #f8f9fa !important;
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
            Leads
        @endslot
        @slot('subtitle')
            Manage your system leads.
        @endslot
        <li class="breadcrumb-item active">Leads</li>
    @endcomponent

    <div class="container-fluid">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">All Leads</h4>
                    @can('create_leads')
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus me-1"></i> Add New Lead
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
</div>

    <div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-address-card me-2"></i><span id="leadModalLabel">Lead Form</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-form-body">
                    <div class="text-center py-5">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-sticky-note me-2"></i>Lead Notes</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="note-modal-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Convert Confirmation Modal --}}
    <div class="modal fade" id="convertConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(var(--primary-rgb, 48, 142, 135), 0.1);">
                        <i class="fas fa-user-check fs-3" style="color: var(--primary-color, #308e87);"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Convert to Customer?</h5>
                    <p class="text-muted mb-4">Are you sure you want to convert this lead into a customer?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary text-white px-4" id="confirmConvertBtn">
                            <i class="fas fa-check me-1"></i> Convert
                        </button>
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
                    <h5 class="fw-bold mb-2">Delete Lead?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this lead?</p>
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

        let leadModal;
        let noteModal;

        $(document).ready(function() {
            // Menu Active
            $('#leads').addClass('active');
            $('#leads-submenu').addClass('d-block');
            $('.leads-menu').addClass('active');

            const modalEl = document.getElementById('leadModal');
            if (modalEl) leadModal = new bootstrap.Modal(modalEl);

            const noteModalEl = document.getElementById('noteModal');
            if (noteModalEl) noteModal = new bootstrap.Modal(noteModalEl);
        });

        // OPEN ADD LEAD MODAL
        function openAddModal() {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#leadAddStatusModal, #leadAddSourceModal').remove();

            $('#leadModalLabel').text(@json(__('Add New Lead')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            leadModal.show();

            $.ajax({
                url: "{{ route('leads.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    if (typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Failed to load form. Please try again.')));
                    }
                    leadModal.hide();
                }
            });
        }

        // OPEN EDIT LEAD MODAL
        function editLead(id) {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#leadAddStatusModal, #leadAddSourceModal').remove();

            var url = "{{ route('leads.edit', ':id') }}".replace(':id', id);
            $('#leadModalLabel').text(@json(__('Edit Lead')));
            $('#modal-form-body').html(
                '<div class="text-center py-4"><p class="mt-2 text-muted mb-0">Loading...</p></div>');
            leadModal.show();

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    if (typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Failed to load data. Please try again.')));
                    }
                    leadModal.hide();
                }
            });
        }

        // OPEN NOTE MODAL
        function addNote(id) {
            var url = "{{ route('leads.note.create', ':id') }}".replace(':id', id);

            $('#note-modal-body').html(
                '<div class="text-center py-4"><div class="spinner-border text-warning"></div></div>');
            noteModal.show();
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#note-modal-body').html(data);
                },
                error: function() {
                    if (typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Failed to load notes.')));
                    }
                    noteModal.hide();
                }
            });
        }

        // Handle AJAX Form Submissions
        $(document).on('submit', '#lead-create-form, #lead-edit-form, #lead-note-form', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.text();
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>');

            // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        leadModal.hide();
                        noteModal.hide();

                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');

                        // Show Toast
                        if (typeof showFloatingAlert === 'function') {
                            showFloatingAlert('success', response.message);
                        }

                        // Reload DataTable without refreshing page
                        $('#leads-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(originalBtnText);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key).addClass('is-invalid');
                            $('#' + key).after('<div class="invalid-feedback">' + value[0] +
                                '</div>');
                        });
                    } else {
                        if (typeof showFloatingAlert === 'function') {
                            showFloatingAlert('danger', @json(__('Something went wrong! Please check console.')));
                        }
                        console.error(xhr.responseText);
                    }
                }
            });
        });

        // MODAL CONFIRMATIONS FOR CONVERT & DELETE
        let formToSubmit = null;

        $(document).on('click', '.convert-btn', function(e) {
            e.preventDefault();
            formToSubmit = $(this).closest('form');
            $('#convertConfirmModal').modal('show');
        });

        $(document).on('click', '#confirmConvertBtn', function() {
            if (formToSubmit) {
                $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Converting...').prop('disabled', true);
                formToSubmit.submit();
            }
        });

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
