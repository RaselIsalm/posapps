@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Quotation List
@endsection

@push('css')
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
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

        #quotation-table {
            border-collapse: separate;
            border-spacing: 0 5px;
        }

        #quotation-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
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
            Quotations
        @endslot
        @slot('subtitle')
            Manage all your quotations here
        @endslot
        <li class="breadcrumb-item active">Quotation List</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark"><i class="fas fa-list me-2"></i> Quotation List</h4>
                        <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Create Quotation
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover w-100 align-middle']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details modal --}}
    <div class="modal fade" id="showQuotationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg p-0 overflow-hidden">
                <div class="modal-body p-0" id="modal-content-area">
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
                    <h5 class="fw-bold mb-2">Delete Quotation?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this quotation?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4"
                            data-bs-dismiss="modal">Cancel</button>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {
            function loadQuotationDetails(id) {
                openGlobalInvoiceModal("{{ url('quotations/show') }}/" + id);
            }

            $(document).on('click', '.view-quotation', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                if(id) loadQuotationDetails(id);
            });

            $(document).on('click', '#quotation-table tbody tr', function(e) {
                if ($(e.target).closest('.dropdown').length || $(e.target).closest('form').length || $(e.target).closest('a').length || $(e.target).is('button')) {
                    return;
                }
                let id = $(this).find('.view-quotation').data('id');
                if (id) loadQuotationDetails(id);
            });

            // Delete Handling
            function showFloatingAlert(type, message) {
                var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
                var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
                $('body').append($alert);
                setTimeout(function() {
                    $alert.remove();
                }, 5000);
            }

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
                        window.LaravelDataTables?.['quotation-table']?.ajax.reload(null, false);
                        showFloatingAlert('success', res.message || @json(__('Quotation deleted successfully.')));
                    },
                    error: function(xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        let errorMsg = @json(__('Failed to delete quotation.'));
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
        });

        // Use global printInvoiceSection from global-invoice.js
        window.printQuotationSection = function () {
            if (typeof printInvoiceSection === 'function') {
                printInvoiceSection();
            }
        }
    </script>
@endpush
