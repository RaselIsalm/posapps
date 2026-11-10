@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Delivery
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .status-card {
            border: 2px solid #dee2e6;
            transition: all 0.2s ease-in-out;
            background: #fff;
            color: #495057;
        }
        .status-card:hover {
            border-color: #adb5bd;
            background: #f8f9fa;
        }
        .btn-check:checked + .status-card {
            border-color: var(--primary-color, #308e87);
            background: rgba(var(--primary-rgb, 48, 142, 135), 0.08);
            color: var(--primary-color, #308e87);
        }
        .btn-check:checked + .status-card i {
            color: var(--primary-color, #308e87) !important;
        }
        #delivery-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #delivery-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .dataTables_wrapper .dt-buttons {
            position: relative;
            top: -16px;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        @media (max-width: 767.98px) {
            /* Stack Search and Buttons on Mobile */
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
            div.dt-buttons {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                gap: 5px;
                margin-bottom: 10px;
                float: none;
                margin-left: 0;
            }
            .dataTables_wrapper .dt-buttons {
                position: static !important;
                top: auto !important;
            }
            div.dt-buttons .btn {
                flex: 1;
                margin: 0 !important;
                padding: 8px 4px;
                font-size: 13px;
            }
            .table-responsive {
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch;
                border: 0;
            }
            .table thead th, .table tbody td {
                white-space: nowrap; /* Prevent squashing columns */
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
            /* Hide theme-injected pseudo-element arrows */
            .dataTables_paginate .pagination .page-item::before,
            .dataTables_paginate .pagination .page-item::after,
            .dataTables_paginate .pagination .page-link::before,
            .dataTables_paginate .pagination .page-link::after {
                display: none !important;
                content: none !important;
            }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Delivery Management
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Delivery Management</li>
    @endcomponent
    <div class="container-fluid">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Filter by Courier</label>
                        <div class="input-group del-field-group">
                            <select id="filter_courier" class="form-select">
                                <option value="">All Couriers</option>
                                @foreach ($couriers as $courier)
                                    <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                                @endforeach
                            </select>
                            @can('manage_api_integrations')
                                <button type="button" class="btn btn-outline-primary btn-del-add"
                                    onclick="openDelCourierModal()" title="Add New Courier">
                                    <i class="fas fa-plus"></i>
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Filter by Status</label>
                        <select id="filter_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped / In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Date Range</label>
                        <input type="text" id="filter_date" class="form-control" placeholder="Select Date Range">
                    </div>
                </div>
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end mt-3">
                    <button id="btn-filter" class="btn btn-primary btn-sm px-4"><i class="fas fa-filter"></i> Apply</button>
                    <button id="btn-reset" class="btn btn-dark btn-sm px-4"><i class="fas fa-undo"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table align-middle w-100']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-sync-alt me-2"></i>Update Delivery Status
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form id="statusForm">
                        @csrf
                        <input type="hidden" id="status_delivery_id" name="delivery_id">
                        
                        <label class="form-label fw-bold text-muted mb-3">Select New Status</label>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_pending" value="pending">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_pending">
                                    <i class="fas fa-clock text-warning fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Pending</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_processing" value="processing">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_processing">
                                    <i class="fas fa-spinner text-info fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Processing</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_shipped" value="shipped">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_shipped">
                                    <i class="fas fa-truck text-primary fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Shipped</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_delivered" value="delivered">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_delivered">
                                    <i class="fas fa-check-circle text-success fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Delivered</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_returned" value="returned">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_returned">
                                    <i class="fas fa-undo text-danger fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Returned</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_return_restocked" value="return_restocked">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_return_restocked">
                                    <i class="fas fa-box-open text-dark fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Restocked</span>
                                </label>
                            </div>
                            <div class="col-12">
                                <input type="radio" class="btn-check" name="status" id="status_cancelled" value="cancelled">
                                <label class="btn w-100 text-start p-3 status-card rounded-3" for="status_cancelled">
                                    <i class="fas fa-times-circle text-danger fs-5 mb-2 d-block"></i>
                                    <span class="fw-bold">Cancelled</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">

                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Delivery Info
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_delivery_id" name="delivery_id">

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2 text-dark">Customer
                                    Info</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Recipient Name</label>
                                <input type="text" name="recipient_name" id="edit_recipient_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Phone Number</label>
                                <input type="text" name="recipient_phone" id="edit_recipient_phone"
                                    class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">Shipment Details
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Courier Service</label>
                                <div class="input-group del-field-group">
                                    <select name="courier_id" id="edit_courier_id" class="form-select">
                                        @foreach ($couriers as $courier)
                                            <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_api_integrations')
                                        <button type="button" class="btn btn-outline-primary btn-del-add"
                                            onclick="openDelCourierModal()" title="Add New Courier">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Tracking Code</label>
                                <input type="text" name="tracking_code" id="edit_tracking_code"
                                    class="form-control font-monospace text-primary" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">COD Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                    <input type="number" name="cod_amount" id="edit_cod_amount"
                                        class="form-control fw-bold text-danger">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Delivery Charge</label>
                                <div class="input-group">
                                    <span class="input-group-text text-dark">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                    <input type="number" name="delivery_charge" id="edit_delivery_charge"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Note</label>
                                <input type="text" name="note" id="edit_note" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
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
                    <h5 class="fw-bold mb-2">Delete Delivery?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this delivery record?</p>
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

    {{-- Invoice Modal --}}
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg p-0 overflow-hidden">
                <div class="modal-body p-0" id="invoice_modal_body">
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.delivery.partials.quick_add_courier')
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Override default pagination language to remove arrows
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                }
            }
        });
    </script>
    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {

            flatpickr("#filter_date", {
                mode: "range",
                dateFormat: "Y-m-d"
            });

            const table = window.LaravelDataTables && window.LaravelDataTables['delivery-table'];

            $('#btn-filter').click(function() {
                table?.draw();
            });

            $('#btn-reset').click(function() {
                $('#filter_courier').val('');
                $('#filter_status').val('');
                $('#filter_date').val('');
                table?.draw();
            });

            if (table) {
                table.on('preXhr.dt', function(e, settings, data) {
                    data.courier_id = $('#filter_courier').val();
                    data.status = $('#filter_status').val();
                    data.date_range = $('#filter_date').val();
                });
            }

            $('body').on('click', '#delivery-table tbody tr', function(e) {
                if ($(e.target).closest(
                        'a, button, input, select, .dropdown-toggle, .btn-close, .form-check-input')
                    .length) {
                    return;
                }

                var invoiceLink = $(this).find('a[onclick^="openInvoiceModal"]');

                if (invoiceLink.length > 0) {
                    var onclickValue = invoiceLink.attr('onclick');
                    var saleIdMatch = onclickValue.match(/\d+/);

                    if (saleIdMatch) {
                        openInvoiceModal(saleIdMatch[0]);
                    }
                }
            });

        // Floating Alert Helper
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        window.openStatusModal = function(id, currentStatus) {
            $('#status_delivery_id').val(id);
            // Uncheck all first
            $('input[name="status"]').prop('checked', false);
            // Check the current status
            $('#status_' + currentStatus).prop('checked', true);
            $('#statusModal').modal('show');
        }

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.text();
            btn.prop('disabled', true).text(@json(__('Updating...')));

            $.post("{{ route('delivery.update-status') }}", $(this).serialize(), function(res) {
                if (res.success) {
                    $('#statusModal').modal('hide');
                    table?.ajax.reload(null, false);
                    showFloatingAlert('success', res.message);
                } else {
                    showFloatingAlert('danger', @json(__('Failed to update status')));
                }
                btn.prop('disabled', false).text(originalText);
            }).fail(function() {
                showFloatingAlert('danger', @json(__('Error processing request')));
                btn.prop('disabled', false).text(originalText);
            });
        });

            window.openEditModal = function(id) {

                $('#editForm')[0].reset();
                $.get("{{ route('delivery.edit', ':id') }}".replace(':id', id), function(data) {
                    $('#edit_delivery_id').val(data.id);
                    $('#edit_recipient_name').val(data.recipient_name);
                    $('#edit_recipient_phone').val(data.recipient_phone);
                    $('#edit_address').val(data.address);
                    $('#edit_courier_id').val(data.courier_id);
                    $('#edit_tracking_code').val(data.tracking_code);
                    $('#edit_cod_amount').val(data.cod_amount);
                    $('#edit_delivery_charge').val(data.delivery_charge);
                    
                    window.currentEditBaseCod = (parseFloat(data.cod_amount) || 0) - (parseFloat(data.delivery_charge) || 0);
                    $('#edit_note').val(data.note);

                    $('#editModal').modal('show');
                }).fail(function() {
                    showFloatingAlert('danger', @json(__('Error loading delivery data!')));
                });
            }
            
            let editCourierCharges = @json($couriers->pluck('default_charge', 'id'));
            window.editCourierCharges = editCourierCharges;

            const editModalEl = document.getElementById('editModal');
            if (editModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                window.deliveryEditModalInstance = bootstrap.Modal.getOrCreateInstance(editModalEl);
            }

            $('#edit_courier_id').on('change', function() {
                let courierId = $(this).val();
                let charge = parseFloat(window.editCourierCharges[courierId]) || 0;
                $('#edit_delivery_charge').val(charge);
                
                let baseCod = window.currentEditBaseCod || 0;
                if (baseCod > 0) {
                    $('#edit_cod_amount').val((baseCod + charge).toFixed(2));
                }
            });

            $('#edit_delivery_charge').on('input', function() {
                let charge = parseFloat($(this).val()) || 0;
                let baseCod = window.currentEditBaseCod || 0;
                if (baseCod > 0) {
                    $('#edit_cod_amount').val((baseCod + charge).toFixed(2));
                }
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                let originalText = btn.text();
                btn.prop('disabled', true).text(@json(__('Saving...')));

                $.ajax({
                    url: "{{ route('delivery.update') }}",
                    type: "PUT",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#editModal').modal('hide');
                            table?.ajax.reload(null, false);
                            showFloatingAlert('success', res.message);
                        } else {
                            showFloatingAlert('danger', @json(__('Failed to update!')));
                        }
                        btn.prop('disabled', false).text(originalText);
                    },
                    error: function(xhr) {
                        let errorMsg = @json(__('An error occurred'));
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showFloatingAlert('danger', @json(__('Error:')) + ' ' + errorMsg);
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });

            window.openInvoiceModal = function(saleId) {
                openGlobalInvoiceModal("{{ route('delivery.invoice', ':id') }}".replace(':id', saleId));
            }

            window.syncSteadfastStatus = function(deliveryId) {
                $.post("{{ route('delivery.sync-steadfast') }}", {
                    _token: '{{ csrf_token() }}',
                    delivery_id: deliveryId
                }, function(res) {
                    if (res.success) {
                        showFloatingAlert('success', res.message);
                        table?.ajax.reload(null, false);
                    } else {
                        showFloatingAlert('danger', res.message || @json(__('Failed to sync status')));
                    }
                }).fail(function(xhr) {
                    let msg = xhr.responseJSON?.message || @json(__('Request failed'));
                    showFloatingAlert('danger', msg);
                });
            }

            window.checkSteadfastBalance = function(courierId) {
                let balanceEl = document.getElementById('sf-balance');
                if (balanceEl) balanceEl.textContent = '...';

                $.post("{{ route('delivery.steadfast-balance') }}", {
                    _token: '{{ csrf_token() }}',
                    courier_id: courierId
                }, function(res) {
                    if (res.success && balanceEl) {
                        balanceEl.textContent = parseFloat(res.balance).toLocaleString('en-BD');
                    } else {
                        showFloatingAlert('danger', res.message || @json(__('Failed to fetch balance')));
                        if (balanceEl) balanceEl.textContent = 'Error';
                    }
                }).fail(function(xhr) {
                    let msg = xhr.responseJSON?.message || @json(__('Request failed'));
                    showFloatingAlert('danger', msg);
                    if (balanceEl) balanceEl.textContent = 'Error';
                });
            }

        // Delete Logic
        let deleteDeliveryUrl = null;

        window.openDeleteModal = function(url) {
            deleteDeliveryUrl = url;
            $('#deleteConfirmModal').modal('show');
        };

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!deleteDeliveryUrl) {
                return;
            }

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

            $.ajax({
                url: deleteDeliveryUrl,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#deleteConfirmModal').modal('hide');
                    table?.ajax.reload(null, false);
                    showFloatingAlert('success', res.message || @json(__('Delivery deleted successfully!')));
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    let errorMsg = @json(__('Failed to delete delivery.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    deleteDeliveryUrl = null;
                }
            });
        });
        });
    </script>
    @include('backend.components.fraud-check-assets')
@endpush
