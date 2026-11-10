@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Product List
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <style>
        .card {
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: visible !important;
        }

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

        input[type="checkbox"].row-checkbox,
        input[type="checkbox"]#select-all {
            width: 16px;
            height: 16px;
            cursor: pointer;
            margin-top: 0;
            position: relative;
            top: 2px;
        }

        .dropdown-menu {
            z-index: 1050 !important;
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
            Products
        @endslot
        @slot('subtitle')
            View and manage all products in your inventory.
        @endslot
        <li class="breadcrumb-item active">Products</li>
    @endcomponent

    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">All Products</h4>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-danger shadow-sm" id="bulk-delete-btn" style="display:none;">
                                <i class="fas fa-trash me-1"></i> Delete Selected
                            </button>
                            @can('create_products')
                                <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-plus me-1"></i> Create Product
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 pt-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-box me-2"></i>Product Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="product-details-content">
                        <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                        </div>
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
                    <h5 class="fw-bold mb-2">Delete Product?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this product?</p>
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

    {{-- Bulk Delete Confirmation Modal --}}
    <div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Selected Products?</h5>
                    <p class="text-muted mb-4">You are about to delete <strong id="bulkDeleteCount">0</strong> selected products. This action cannot be undone. Are you sure?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmBulkDeleteBtn">
                            <i class="fas fa-trash-alt me-1"></i> Delete All
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>

    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {
            $('#products').addClass('active');
            $('#products-submenu').addClass('d-block');
            $('.product-list-menu').addClass('active');

            const tableId = '#products-table';

            $(document).on('click', '#select-all', function() {
                var isChecked = this.checked;
                $('.row-checkbox').prop('checked', isChecked);
                toggleBulkDeleteBtn();
            });

            $(document).on('change', '.row-checkbox', function() {
                if (!this.checked) {
                    $('#select-all').prop('checked', false);
                }
                toggleBulkDeleteBtn();
            });

            function toggleBulkDeleteBtn() {
                if ($('.row-checkbox:checked').length > 0) {
                    $('#bulk-delete-btn').fadeIn();
                } else {
                    $('#bulk-delete-btn').fadeOut();
                }
            }

            let bulkDeleteIds = [];
            $('#bulk-delete-btn').on('click', function() {
                bulkDeleteIds = [];
                $('.row-checkbox:checked').each(function() {
                    bulkDeleteIds.push($(this).data('id'));
                });

                if (bulkDeleteIds.length > 0) {
                    $('#bulkDeleteCount').text(bulkDeleteIds.length);
                    $('#bulkDeleteConfirmModal').modal('show');
                }
            });

            $(document).on('click', '#confirmBulkDeleteBtn', function() {
                if (bulkDeleteIds.length === 0) return;

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

                $.ajax({
                    url: '{{ route('products.bulkDelete') }}',
                    method: 'POST',
                    data: {
                        ids: bulkDeleteIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#bulkDeleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);
                        
                        $(tableId).DataTable().ajax.reload();
                        $('#select-all').prop('checked', false);
                        $('#bulk-delete-btn').hide();
                        
                        // Show success alert
                        showFloatingAlert('success', response.message || @json(__('Products deleted successfully.')));
                    },
                    error: function(xhr) {
                        $('#bulkDeleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);
                        showFloatingAlert('danger', @json(__('Something went wrong!')));
                    }
                });
            });

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
                if (!deleteUrl) return;

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);
                        
                        $(tableId).DataTable().ajax.reload(null, false);
                        showFloatingAlert('success', response.message || @json(__('Product deleted successfully.')));
                    },
                    error: function(xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        btn.html(originalText).prop('disabled', false);
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            showFloatingAlert('danger', xhr.responseJSON.message);
                        } else {
                            showFloatingAlert('danger', @json(__('Something went wrong! Please try again.')));
                        }
                    }
                });
            });

            $(document).on('click', 'tr.clickable-row', function(e) {
                if ($(e.target).closest('input[type="checkbox"], .dropdown, button, a').length > 0) {
                    return;
                }

                let id = $(this).data('id');
                let url = "{{ route('products.show', ':id') }}";
                url = url.replace(':id', id);

                $('#productDetailsModal').modal('show');
                $('#product-details-content').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#product-details-content').html(response);
                    },
                    error: function() {
                        $('#product-details-content').html(
                            '<div class="text-danger text-center p-3">Something went wrong!</div>'
                        );
                    }
                });
            });
        });
    </script>
@endpush
