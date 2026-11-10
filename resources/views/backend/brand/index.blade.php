@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Brands
@endsection

@push('css')
    {{-- DataTables CSS --}}
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

        #brand-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #brand-table tbody tr:hover {
            background-color: #f8f9fa !important;
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
                margin-top: 10px;
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
            Brands
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Brands</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <h4>All Brands</h4>
                            @can('create_brands')
                                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                                    <i class="fas fa-plus me-1"></i> Add New Brand
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table w-100', 'width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-tags me-2"></i><span id="modalTitle">Brand</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-form-body">
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
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
                    <h5 class="fw-bold mb-2">Delete Brand?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this brand?</p>
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
        // Menu Active Logic
        $('#products').addClass('active');
        $('#products-submenu').addClass('d-block');
        $('.brand-menu').addClass('active');

        // Global Modal Variable
        let brandModal;

        $(document).ready(function() {
            // Initialize Modal
            const modalEl = document.getElementById('brandModal');
            if (modalEl) {
                brandModal = new bootstrap.Modal(modalEl);
            }
        });

        // Open Create Modal
        function openCreateModal() {
            $('#modalTitle').text(@json(__('Create New Brand')));
            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            brandModal.show();

            $.ajax({
                url: "{{ route('brands.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Failed to load form.')));
                    } else if(typeof alert !== 'undefined') {
                        alert(@json(__('Failed to load form.')));
                    }
                    brandModal.hide();
                }
            });
        }

        // Open Edit Modal
        function editBrand(id) {
            $('#modalTitle').text(@json(__('Edit Brand')));
            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            brandModal.show();

            var url = "{{ route('brands.edit', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Failed to load data.')));
                    } else if(typeof alert !== 'undefined') {
                        alert(@json(__('Failed to load data.')));
                    }
                    brandModal.hide();
                }
            });
        }

        // Handle Form Submission via AJAX
        $(document).on('submit', '#brand-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    brandModal.hide();
                    $('#brand-table').DataTable().ajax.reload(null, false);
                    
                    let msg = response.message || @json(__('Brand saved successfully.'));
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('success', msg);
                    } else if(typeof alert !== 'undefined') {
                        alert(msg);
                    }
                },
                error: function(xhr) {
                    let msg = @json(__('Something went wrong! Please try again.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', msg);
                    } else if(typeof alert !== 'undefined') {
                        alert(msg);
                    }
                }
            });
        });

        // Handle Delete Modal
        let deleteUrl = '';
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
                    
                    $('#brand-table').DataTable().ajax.reload(null, false);
                    
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('success', response.message || @json(__('Brand deleted successfully.')));
                    } else if(typeof alert !== 'undefined') {
                        alert(response.message || @json(__('Brand deleted successfully.')));
                    }
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    btn.html(originalText).prop('disabled', false);
                    
                    let msg = @json(__('Something went wrong! Please try again.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', msg);
                    } else if(typeof alert !== 'undefined') {
                        alert(msg);
                    }
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
    </script>
@endpush
