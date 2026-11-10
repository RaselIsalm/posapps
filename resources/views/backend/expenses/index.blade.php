@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Expenses
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
            Expenses
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Expenses</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">All Expenses</h4>
                        @can('create_expenses')
                            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                                <i class="fas fa-plus me-1"></i> Add New Expense
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-receipt me-2"></i><span id="modalTitle">Expense</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-form-body">
                    {{-- Loader --}}
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Expense?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this expense?</p>
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
        // Menu Active
        $('#expenses').addClass('active');
        $('#expenses-submenu').addClass('d-block');
        $('.expenses-menu').addClass('active');

        // Open Create Modal
        function openCreateModal() {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#expAddCategoryModal, #expAddBranchModal, #expAddAccountModal').remove();

            $('#modalTitle').text(@json(__('Create New Expense')));
            $('#expenseModal').modal('show');
            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            $.ajax({
                url: "{{ route('expenses.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    $('#modal-form-body').html(
                        '<div class="text-danger text-center">Failed to load form.</div>');
                }
            });
        }

        // Open Edit Modal
        function editExpense(id) {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#expAddCategoryModal, #expAddBranchModal, #expAddAccountModal').remove();

            $('#modalTitle').text(@json(__('Edit Expense')));
            $('#expenseModal').modal('show');
            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            var url = "{{ route('expenses.edit', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    $('#modal-form-body').html(
                        '<div class="text-danger text-center">Failed to load form.</div>');
                }
            });
        }

        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        function showExpenseFormError(message) {
            var $box = $('#expenseFormError');
            if (!$box.length) return;
            $box.removeClass('d-none').html(message);
        }

        function clearExpenseFormError() {
            var $box = $('#expenseFormError');
            if (!$box.length) return;
            $box.addClass('d-none').empty();
        }

        // AJAX create/update — no full page reload inside modal
        $(document).off('submit.expenseForm', '#expenseCreateForm, #expenseEditForm')
            .on('submit.expenseForm', '#expenseCreateForm, #expenseEditForm', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var originalHtml = $btn.html();
                clearExpenseFormError();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        if (res && res.success) {
                            $('#expenseModal').modal('hide');
                            $('#expAddCategoryModal, #expAddBranchModal, #expAddAccountModal').remove();
                            window.LaravelDataTables?.['expenses-table']?.ajax.reload(null, false);
                            showFloatingAlert('success', res.message || @json(__('Saved successfully.')));
                            return;
                        }
                        showExpenseFormError((res && res.message) || @json(__('Could not save expense.')));
                    },
                    error: function (xhr) {
                        var msg = @json(__('Could not save expense.'));
                        if (xhr.status === 422 && xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                var parts = [];
                                $.each(xhr.responseJSON.errors, function (_, arr) {
                                    if (arr && arr[0]) parts.push(arr[0]);
                                });
                                msg = parts.length ? parts.join('<br>') : (xhr.responseJSON.message || msg);
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showExpenseFormError(msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });

                return false;
            });

        let deleteUrl = null;

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            deleteUrl = $(this).data('url');
            $('#deleteConfirmModal').modal('show');
        });

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!deleteUrl) {
                return;
            }

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#deleteConfirmModal').modal('hide');
                    window.LaravelDataTables?.['expenses-table']?.ajax.reload(null, false);
                    showFloatingAlert('success', res.message || @json(__('Expense deleted successfully.')));
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    let errorMsg = @json(__('Failed to delete expense.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    deleteUrl = null;
                }
            });
        });
    </script>
@endpush
