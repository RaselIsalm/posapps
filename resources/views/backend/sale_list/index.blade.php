@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Sale List
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

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

        .bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.1) !important;
            color: #3b82f6 !important;
        }

        .bg-soft-info {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }

        .badge-soft-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-soft-warning {
            background-color: #fef3c7;
            color: #b45309;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
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

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 13px;
            color: #555;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        .dropdown-toggle::after {
            display: none;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        #sale-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #sale-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        @media (max-width: 767.98px) {

            /* Stack Search and Buttons on Mobile */
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
                /* Prevent squashing columns */
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
    Sale List
    @endslot
    @slot('subtitle')
    Manage and configure your system's fundamental preferences and parameters.
    @endslot
    <li class="breadcrumb-item active"> Sale List</li>
    @endcomponent
    <div class="container-fluid">
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                style="border-bottom: 1px solid #f0f0f0; cursor: pointer;" data-bs-toggle="collapse"
                data-bs-target="#advancedFilter">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filter</h6>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="collapse show" id="advancedFilter">
                <div class="card-body bg-light">
                    <div class="row">
                        {{-- Branch --}}
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Branch</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-code-branch"></i></span>
                                <select id="branch_id" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="filter-label">Date Range</label>
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                <input type="date" id="start_date" class="form-control w-100">
                                <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                <input type="date" id="end_date" class="form-control w-100">
                            </div>
                        </div>

                        {{-- Payment Status --}}
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-list-ul"></i></span>
                                <select class="form-select" id="payment_status">
                                    <option value="">All Status</option>
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partial</option>
                                    <option value="due">Due</option>
                                </select>
                            </div>
                        </div>

                        {{-- Filter Buttons --}}
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="button" id="btn-filter" class="btn btn-primary w-100 text-white">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <button type="button" id="btn-reset" class="btn btn-dark w-100 text-white">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table w-100', 'width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice modal is global: backend.components.invoice.shell --}}

    {{-- Delivery Modal --}}
    <div class="modal fade" id="shippingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-truck-loading me-2"></i>Create Shipment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="shippingForm">
                    @csrf
                    <input type="hidden" name="sale_id" id="ship_sale_id">

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <small class="text-muted text-uppercase fw-bold">Invoice Amount</small>
                                    <h3 class="mb-0 fw-bold text-dark" id="ship_grand_total">{{ \App\Helpers\CurrencyHelper::format(0) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded border text-center text-white" id="payment_status_box">
                                    <small class="text-white-50 text-uppercase fw-bold">Payment Status</small>
                                    <h4 class="mb-0 fw-bold" id="ship_payment_status">Checking...</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 border-end pe-md-4">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Recipient Details</h6>

                                <div class="mb-2">
                                    <label class="small fw-bold text-muted">Customer Name</label>
                                    <input type="text" name="recipient_name" id="ship_name" class="form-control fw-bold"
                                        required>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="small fw-bold text-muted mb-0">Phone Number</label>
                                        <a href="javascript:void(0)" class="text-primary small fw-bold"
                                            onclick="checkFraud($('#ship_phone').val())">
                                            <i class="fas fa-shield-alt"></i> Full report
                                        </a>
                                    </div>
                                    <input type="text" name="recipient_phone" id="ship_phone" class="form-control fw-bold"
                                        required>
                                    <div id="shipFraudPanel" class="fraud-inline-panel mt-2 d-none"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">Delivery Address</label>
                                    <textarea name="address" id="ship_address" class="form-control" rows="2"
                                        required></textarea>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Courier Settings</h6>

                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">Select Courier</label>
                                    <div class="ship-field-group">
                                        <select name="courier_id" id="ship_courier" class="form-select" required
                                            onchange="updateDeliveryCharge()">
                                            <option value="">Choose Courier...</option>
                                        </select>
                                        @can('manage_api_integrations')
                                            <button type="button" class="btn btn-outline-primary btn-ship-add"
                                                onclick="openShipCourierModal()" title="Add New Courier">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">COD Amount <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-danger fw-bold">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                            <input type="number" name="cod_amount" id="ship_cod_amount"
                                                class="form-control fw-bold text-danger" value="0" step="0.01">
                                        </div>
                                        <small class="text-muted" style="font-size: 10px;">Collection Amount</small>
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Delivery Charge</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary fw-bold">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                            <input type="number" name="delivery_charge" id="ship_delivery_charge"
                                                class="form-control fw-bold" value="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">Tracking / Memo No (Optional)</label>
                                    <input type="text" name="tracking_code" class="form-control"
                                        placeholder="Leave empty for auto-generate">
                                </div>

                                <div class="mb-2">
                                    <label class="small fw-bold text-muted">Note</label>
                                    <input type="text" name="note" class="form-control" placeholder="e.g. Fragile item">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-1"></i> Confirm Shipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Due Payment Modal --}}
    <div class="modal fade" id="duePaymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Pay Due Amount</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="duePaymentForm">
                    @csrf
                    <input type="hidden" name="sale_id" id="due_sale_id">

                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded border text-center mb-4">
                            <small class="text-muted text-uppercase fw-bold">Total Due Amount</small>
                            <h3 class="mb-0 fw-bold text-danger" id="due_display_amount">{{ \App\Helpers\CurrencyHelper::format(0) }}</h3>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Paying Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                <input type="number" name="amount" id="due_paying_amount" class="form-control fw-bold"
                                    step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-muted" for="due_payment_method">Payment Account <span
                                    class="text-danger">*</span></label>
                            <div class="input-group flex-nowrap pur-field-group">
                                <select name="payment_method" id="due_payment_method" class="form-select" required>
                                    @forelse ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @empty
                                        <option value="" disabled selected>No payment accounts found</option>
                                    @endforelse
                                </select>
                                @can('create_accounts')
                                    <button class="btn btn-outline-primary btn-pur-add" type="button"
                                        onclick="openPurAccountModal()" title="Add Payment Account"
                                        aria-label="Add Payment Account">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Payment Note / Trx ID (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="Enter Trx ID or note">
                        </div>

                        <div class="mb-2">
                            <label class="small fw-bold text-muted">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm text-white">
                            <i class="fas fa-check-circle me-1"></i> Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Custom Delete Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('Are you sure?') }}</h5>
                    <p class="text-muted mb-4">{{ __('This will void the sale. Stock and account payments are fully reversed. The invoice stays for audit. Prefer Sale Return for a partial refund.') }}</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <form id="deleteForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-ban me-1"></i> {{ __('Yes, Void') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.purchase.partials.quick_add_account', [
        'paymentMethodSelectId' => 'due_payment_method',
        'parentModalId' => 'duePaymentModal',
    ])
    @include('backend.sale_list.partials.quick_add_ship_courier')
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
        function showCustomToast(message, type = 'success') {
            let title = type === 'success' ? @json(__('Success!')) : (type === 'danger' || type === 'error' ? @json(__('Error!')) : @json(__('Warning!')));
            let alertClass = type === 'error' ? 'danger' : type;
            let alertHtml = '<div class="alert alert-' + alertClass + ' floating-alert"><strong>' + title + '</strong> ' + message + '</div>';
            $('body').append(alertHtml);
            setTimeout(function() {
                $('.floating-alert').last().remove();
            }, 5000);
        }

        $(document).ready(function () {
            const table = window.LaravelDataTables && window.LaravelDataTables['sale-table'];
            
            // Check for edit success message in localStorage
            let successMsg = localStorage.getItem('sale_success_msg');
            if (successMsg) {
                showCustomToast(successMsg, 'success');
                localStorage.removeItem('sale_success_msg');
            }

            // Filter Button
            $('#btn-filter').click(function () {
                table?.draw();
            });

            // Reset Button
            $('#btn-reset').click(function () {
                $('#branch_id').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#payment_status').val('');
                table?.draw();
            });

            // Pass Filter Values to Controller
            table?.on('preXhr.dt', function (e, settings, data) {
                data.branch_id = $('#branch_id').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.payment_status = $('#payment_status').val();
            });

            // Row Click Event
            $('#sale-table tbody').on('click', 'tr', function (e) {
                if ($(e.target).closest('button, a, .dropdown, .btn').length) {
                    return;
                }
                let rowData = table.row(this).data();
                if (rowData) {
                    showInvoiceModal(rowData.id);
                }
            });

            // Action Button Click Event
            $('#sale-table tbody').on('click', '.show-invoice', function (e) {
                e.preventDefault();
                let saleId = $(this).data('id');
                showInvoiceModal(saleId);
            });

            function showInvoiceModal(saleId) {
                let url = "{{ route('sale-list.show', ':id') }}".replace(':id', saleId);
                openGlobalInvoiceModal(url);
            }
        });

        // Delete Button Click Event
        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let url = $(this).data('url');
            $('#deleteForm').attr('action', url);
            let myModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            myModal.show();
        });
        // Global variable to store default charges
        let courierCharges = {};
        window.courierCharges = courierCharges;

        // Open Modal & Fetch Data
        window.openShippingModal = function (saleId) {
            // Reset UI
            $('#ship_sale_id').val(saleId);
            $('#shippingForm')[0].reset();
            $('#shipFraudPanel').addClass('d-none').empty();
            $('#ship_payment_status').html('<i class="fas fa-spinner fa-spin"></i> Checking...');
            $('#payment_status_box').removeClass('bg-success bg-warning text-dark').addClass('bg-secondary text-white');

            const shipModalEl = document.getElementById('shippingModal');
            if (shipModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                window.shippingModalInstance = bootstrap.Modal.getOrCreateInstance(shipModalEl);
                window.shippingModalInstance.show();
            } else {
                $('#shippingModal').modal('show');
            }

            // Fetch Data
            $.ajax({
                url: "{{ route('delivery.get-sale-info', ':id') }}".replace(':id', saleId),
                type: 'GET',
                success: function (response) {
                    // Fill Data
                    $('#ship_name').val(response.recipient_name);
                    $('#ship_phone').val(response.recipient_phone);
                    $('#ship_address').val(response.address);
                    $('#ship_grand_total').text(formatCurrency(response.grand_total));

                    // COD Logic
                    window.currentBaseCod = parseFloat(response.cod_amount) || 0;
                    $('#ship_cod_amount').val(window.currentBaseCod.toFixed(2));

                    if (parseFloat(response.cod_amount) <= 0) {
                        // Prepaid
                        $('#payment_status_box').removeClass('bg-secondary bg-warning text-dark').addClass(
                            'bg-success text-white');
                        $('#ship_payment_status').html('<i class="fas fa-check-circle"></i> ' + @json(__('PAID (No COD)')));
                        $('#ship_cod_amount').prop('readonly', true);
                    } else {
                        // COD
                        $('#payment_status_box').removeClass('bg-secondary bg-success text-white').addClass(
                            'bg-warning text-dark');
                        $('#ship_payment_status').html(
                            '<i class="fas fa-exclamation-triangle"></i> ' + @json(__('COD:')) + ' {{ \App\Helpers\CurrencyHelper::getSymbol() }}' +
                            response.cod_amount);
                        $('#ship_cod_amount').prop('readonly', false);
                    }

                    // Courier Dropdown
                    let courierSelect = $('#ship_courier');
                    courierSelect.html('<option value="">' + @json(__('Choose Courier...')) + '</option>');
                    courierCharges = {};
                    window.courierCharges = courierCharges;

                    (response.couriers || []).forEach(function (courier) {
                        courierCharges[courier.id] = courier.default_charge;
                        courierSelect.append(
                            `<option value="${courier.id}">${courier.name}</option>`);
                    });

                    if (window.shipFraud && typeof window.shipFraud.run === 'function') {
                        window.shipFraud.run();
                    }
                },
                error: function () {
                    showCustomToast(@json(__('Could not fetch sale data.')), 'error');
                    $('#shippingModal').modal('hide');
                }
            });
        }

        // Update Charge
        window.updateDeliveryCharge = function () {
            let courierId = $('#ship_courier').val();
            let charge = 0;
            if (courierId && courierCharges[courierId]) {
                charge = parseFloat(courierCharges[courierId]) || 0;
            }
            $('#ship_delivery_charge').val(charge);
            
            // Re-calculate COD
            let baseCod = window.currentBaseCod || 0;
            if (baseCod > 0) {
                $('#ship_cod_amount').val((baseCod + charge).toFixed(2));
            }
        }

        $('#ship_delivery_charge').on('input', function() {
            let charge = parseFloat($(this).val()) || 0;
            let baseCod = window.currentBaseCod || 0;
            if (baseCod > 0) {
                $('#ship_cod_amount').val((baseCod + charge).toFixed(2));
            }
        });

        // Submit Form
        $('#shippingForm').on('submit', function (e) {
            e.preventDefault();

            const form = this;
            const btn = $(form).find('button[type="submit"]');
            const originalText = btn.html();

            const sendShipment = function () {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: "{{ route('delivery.store') }}",
                    type: "POST",
                    data: $(form).serialize(),
                    success: function (response) {
                        if (response.success) {
                            $('#shippingModal').modal('hide');
                            showCustomToast(@json(__('Shipment created. Tracking: :code')).replace(':code', response.tracking_code), 'success');
                            window.LaravelDataTables?.['sale-table']?.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        let err = JSON.parse(xhr.responseText);
                        showCustomToast(@json(__('Error: ')) + (err.message || @json(__('Something went wrong'))), 'error');
                    },
                    complete: function () {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            };

            const fraudState = window.FraudCheck ? window.FraudCheck.getShipState() : {};
            if (fraudState.level === 'high' && !fraudState.acknowledged) {
                window.FraudCheck.confirmHighRisk(function () {
                    fraudState.acknowledged = true;
                    sendShipment();
                });
                return;
            }

            sendShipment();
        });

        // Open Due Payment Modal
        window.openDueModal = function (saleId, dueAmount) {
            $('#due_sale_id').val(saleId);
            $('#due_display_amount').text(formatCurrency(parseFloat(dueAmount)));
            $('#due_paying_amount').val(parseFloat(dueAmount).toFixed(2));

            $('#due_paying_amount').attr('max', parseFloat(dueAmount).toFixed(2));

            $('#duePaymentForm')[0].reset();
            $('#due_sale_id').val(saleId);
            $('#due_paying_amount').val(parseFloat(dueAmount).toFixed(2));

            $('#duePaymentModal').modal('show');
        }

        // Handle Due Payment Form Submit
        $('#duePaymentForm').on('submit', function (e) {
            e.preventDefault();

            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: "{{ route('sale-list.add-payment') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        $('#duePaymentModal').modal('hide');
                        showCustomToast(@json(__('Payment Added Successfully!')), 'success');
                        window.LaravelDataTables?.['sale-table']?.ajax.reload(null, false);
                    } else {
                        showCustomToast(@json(__('Error: ')) + response.message, 'error');
                    }
                },
                error: function (xhr) {
                    let err = JSON.parse(xhr.responseText);
                    showCustomToast(@json(__('Error: ')) + (err.message || @json(__('Failed to add payment'))), 'error');
                },
                complete: function () {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
        // =========================================
        // Copy Invoice Number
        // =========================================
        function copyInvoice(el, event) {
            if (event) {
                event.stopPropagation();
            }
            const inv = el.getAttribute('data-inv');
            navigator.clipboard.writeText(inv).then(() => {
                // Visual feedback
                el.classList.remove('far', 'fa-copy', 'text-muted');
                el.classList.add('fas', 'fa-check', 'text-success');
                el.title = @json(__('Copied!'));

                showCustomToast(@json(__('Invoice :no copied!')).replace(':no', inv), 'success');

                // Reset after 1.5s
                setTimeout(() => {
                    el.classList.remove('fas', 'fa-check', 'text-success');
                    el.classList.add('far', 'fa-copy', 'text-muted');
                    el.title = @json(__('Copy Invoice'));
                }, 1500);
            });
        }
    </script>
    @include('backend.components.fraud-check-assets')
    <script>
        window.shipFraud = window.FraudCheck.bindShipping({
            phoneInput: '#ship_phone',
            panel: '#shipFraudPanel'
        });
    </script>
@endpush