@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Gift Cards
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <style>
        .filter-card {
            border: 1px solid #eef1f6;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 25px;
            background: #fff;
        }

        .filter-header {
            cursor: pointer;
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border-bottom: 1px solid transparent;
            transition: all 0.3s;
        }

        .filter-header:hover {
            background: #fcfcfc;
        }

        .filter-title {
            font-weight: 700;
            color: #343a40;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-body {
            padding: 20px 25px 30px 25px;
            display: none;
            border-top: 1px dashed #e4e6ef;
            background-color: #fcfcfc;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #8898aa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            display: block;
        }

        .form-control-custom {
            border: 1px solid #e4e6ef;
            color: #5e6278;
            font-weight: 500;
            font-size: 13px;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.1);
        }

        .input-group-text-custom {
            background-color: #fff;
            border-color: #e4e6ef;
            color: #b5b5c3;
            font-size: 13px;
            border-left: 0;
            border-right: 0;
        }

        .btn-filter-custom {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 10px 25px;
            font-size: 13px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-filter-custom:hover {
            background-color: var(--primary-hover, #256e69);
            color: #fff;
        }

        .btn-reset-custom {
            background-color: #2c323f;
            color: white;
            font-weight: 600;
            border: none;
            padding: 10px 25px;
            font-size: 13px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-reset-custom:hover {
            background-color: #1b1f27;
            color: white;
        }

        .table thead th {
            background-color: #f9f9f9;
            color: #6c757d;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 13px;
            padding: 15px;
            color: #3f4254;
            border-bottom: 1px solid #f9f9f9;
        }

        div.dt-buttons {
            float: right;
            margin-left: 15px;
        }

        .table-responsive {
            overflow: visible !important;
        }

        /* Badges */
        .badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-soft-success {
            background: #e8fff3;
            color: #50cd89;
        }

        .badge-soft-danger {
            background: #fff5f8;
            color: #f1416c;
        }

        .badge-soft-warning {
            background: #fff8dd;
            color: #ffc700;
        }

        .badge-soft-secondary {
            background: #e4e6ef;
            color: #5e6278;
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
            Gift Cards
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Gift Cards</li>
    @endcomponent
    <div class="container-fluid">

        <div class="card filter-card">
            <div class="filter-header" onclick="$('#filterBody').slideToggle()">
                <div class="filter-title">
                    <i class="fas fa-filter" style="color: var(--primary-color);"></i>
                    Advanced Filter
                </div>
                <i class="fas fa-chevron-down text-muted"></i>
            </div>

            <div class="filter-body" id="filterBody">
                <div class="row align-items-end g-3">

                    <div class="col-md-5 mb-3 mb-md-0">
                        <label class="filter-label">DATE RANGE</label>
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                            <input type="date" id="filter_start_date" class="form-control form-control-custom w-100">
                            <span class="text-muted fw-bold d-none d-sm-block">to</span>
                            <input type="date" id="filter_end_date" class="form-control form-control-custom w-100">
                        </div>
                    </div>

                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="filter-label">STATUS</label>
                        <select id="filter_status" class="form-select form-control-custom">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="used">Used / Empty</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button id="btn-filter" class="btn btn-filter-custom w-100">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                            <button id="btn-reset" class="btn btn-reset-custom w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-white py-4 border-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h4 class="m-0 fw-bold text-dark">All Gift Cards</h4>
                    <div class="d-flex flex-wrap gap-2">
                        @if (\App\Services\FeatureService::getActive('gift_cards'))
                            <button class="btn btn-primary shadow-sm" onclick="openGiftCardModal()" style="background-color: var(--primary-color); border-color: var(--primary-color);">
                                <i class="fas fa-plus me-1"></i> Sell Gift Card
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-4 pt-0">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table w-100', 'width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- View Details Modal --}}
    <div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="fas fa-gift me-2"></i>Card Details</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="viewModalBody">

                    <div class="text-center py-5">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usage History Modal --}}
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Usage History</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="historyModalBody">

                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Gift Card</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="editModalBody">

                </div>
            </div>
        </div>
    </div>

    {{-- Recharge Modal --}}
    <div class="modal fade" id="rechargeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="fas fa-bolt me-2"></i>Recharge Gift Card</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="rechargeModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status"></div>
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
                    <h5 class="fw-bold mb-2">Delete Gift Card?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this gift card?</p>
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

    {{-- Gift Card Modal --}}
    @if (\App\Services\FeatureService::getActive('gift_cards'))
        <div class="modal fade" id="giftCardModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-gift me-2"></i>Sell Gift Card</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="giftCardForm">
                            <div class="mb-3">
                                <label class="small fw-bold text-muted">Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                                    <input type="text" name="card_no" id="gc_card_no" class="form-control fw-bold"
                                        placeholder="Scan or Auto Generate" required>
                                    <button type="button" class="btn btn-outline-primary" onclick="generateGiftCardNo()">
                                        <i class="fas fa-magic"></i> Auto
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="small fw-bold text-muted">Card Value</label>
                                    <input type="number" name="value" id="gc_value" class="form-control fw-bold"
                                        placeholder="5000" required oninput="syncGcPrice()">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-muted">Price</label>
                                    <input type="number" name="price" id="gc_price" class="form-control fw-bold"
                                        placeholder="5000" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold text-muted">Expiry Date</label>
                                <input type="date" name="expiry_date" id="gc_expiry" class="form-control" required
                                    value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold text-muted">Customer</label>
                                <div class="input-group gc-field-group">
                                    <select name="customer_id" id="gc_customer_id" class="form-select">
                                        <option value="">Walk-in Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}
                                                ({{ $customer->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_customers')
                                        <button class="btn btn-outline-primary btn-gc-add" type="button"
                                            onclick="openGcCustomerModal('#gc_customer_id')" title="Add Customer">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold text-muted">Payment Method</label>
                                <div class="input-group gc-field-group">
                                    <select name="payment_method" id="gc_payment_method" class="form-select">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button class="btn btn-outline-primary btn-gc-add" type="button"
                                            onclick="openGcAccountModal('#gc_payment_method')" title="Add Payment Method">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary text-white w-100 py-2 fw-bold rounded-pill shadow-sm">
                                Confirm & Activate <i class="fas fa-check-circle ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('backend.gift_card.partials.quick_add')
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

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
        // Matches the site-wide floating-alert style used for flash messages (backend.layout.partials.alert)
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }
    </script>

    <script>
        let giftCardModalInstance;

        $(document).ready(function() {
            const table = window.LaravelDataTables && window.LaravelDataTables['gift-card-table'];
            const gcModalEl = document.getElementById('giftCardModal');
            if (gcModalEl) {
                giftCardModalInstance = bootstrap.Modal.getOrCreateInstance(gcModalEl);
                window.giftCardModalInstance = giftCardModalInstance;
            }

            // Pass Filter Data
            table?.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#filter_start_date').val();
                data.end_date = $('#filter_end_date').val();
                data.status = $('#filter_status').val();
            });

            // Filter Trigger
            $('#btn-filter').on('click', function() {
                table?.draw();
            });

            // Reset Trigger
            $('#btn-reset').on('click', function() {
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                $('#filter_status').val('all');
                table?.draw();
            });

            // View Details Click Event
            $(document).on('click', '.view-btn', function() {
                let url = $(this).data('url');

                // Show Modal & Loader
                $('#viewDetailsModal').modal('show');
                $('#viewModalBody').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );

                // Fetch Data
                $.get(url, function(data) {
                    $('#viewModalBody').html(data);
                }).fail(function() {
                    $('#viewModalBody').html(
                        '<p class="text-danger text-center">Failed to load details</p>');
                });
            });

            // Row Click Logic
            $(document).on('click', '.clickable-row', function(e) {
                if ($(e.target).closest('button, a, .dropdown-menu, .form-check-input').length) {
                    return;
                }
                let url = $(this).data('url');
                $('#viewDetailsModal').modal('show');
                $('#viewModalBody').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );
                $.get(url, function(data) {
                    $('#viewModalBody').html(data);
                }).fail(function() {
                    $('#viewModalBody').html(
                        '<p class="text-danger text-center">Failed to load details</p>');
                });
            });

        });

        // History Button Click
        $(document).on('click', '.history-btn', function() {
            let url = $(this).data('url');

            $('#historyModal').modal('show');
            $('#historyModalBody').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
            );

            $.get(url, function(data) {
                $('#historyModalBody').html(data);
            }).fail(function() {
                $('#historyModalBody').html(
                    '<p class="text-danger text-center">Failed to load history</p>');
            });
        });

        // Open Edit Modal
        $(document).on('click', '.edit-btn', function() {
            let url = $(this).data('url');
            $('#editModal').modal('show');
            $('#editModalBody').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
            );

            $.get(url, function(data) {
                $('#editModalBody').html(data);
            });
        });

        // Open Recharge Modal
        $(document).on('click', '.recharge-btn', function() {
            let url = $(this).data('url');
            $('#rechargeModal').modal('show');
            $('#rechargeModalBody').html(
                '<div class="text-center py-5"><div class="spinner-border text-success"></div></div>'
            );

            $.get(url, function(data) {
                $('#rechargeModalBody').html(data);
            });
        });

        // Submit Recharge Form
        $(document).on('submit', '#rechargeGiftCardForm', function(e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let btn = form.find('button[type="submit"]');
            let originalText = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize() + '&_token={{ csrf_token() }}',
                success: function(res) {
                    if (res.status === 'success') {
                        $('#rechargeModal').modal('hide');
                        window.LaravelDataTables?.['gift-card-table']?.ajax.reload();
                        showFloatingAlert('success', res.message);
                    } else {
                        showFloatingAlert('danger', res.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = @json(__('Something went wrong!'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Submit Update Form
        $(document).on('submit', '#editGiftCardForm', function(e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let btn = form.find('button[type="submit"]');
            let originalText = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        $('#editModal').modal('hide');
                        window.LaravelDataTables?.['gift-card-table']?.ajax
                            .reload();
                        showFloatingAlert('success', res.message);
                    } else {
                        showFloatingAlert('danger', res.message);
                    }
                },
                error: function(xhr) {
                    showFloatingAlert('danger', @json(__('Validation Error or Server Error')));
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Delete Logic
        let deleteCardUrl = null;

        $(document).on('click', '.delete-btn', function() {
            deleteCardUrl = $(this).data('url');
            $('#deleteConfirmModal').modal('show');
        });

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!deleteCardUrl) {
                return;
            }

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

            $.ajax({
                url: deleteCardUrl,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#deleteConfirmModal').modal('hide');
                    window.LaravelDataTables?.['gift-card-table']?.ajax.reload();
                    showFloatingAlert('success', res.message);
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    let errorMsg = @json(__('Failed to delete gift card.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    deleteCardUrl = null;
                }
            });
        });

        /* --- Gift Card --- */
        function openGiftCardModal() {
            document.getElementById('giftCardForm').reset();
            const nextYear = new Date();
            nextYear.setFullYear(nextYear.getFullYear() + 1);
            document.getElementById('gc_expiry').valueAsDate = nextYear;
            if (giftCardModalInstance) giftCardModalInstance.show();
        }

        function generateGiftCardNo() {
            const year = new Date().getFullYear();
            const random = Math.floor(1000 + Math.random() * 9000);
            document.getElementById('gc_card_no').value = `GC-${year}-${random}`;
        }

        function syncGcPrice() {
            const val = document.getElementById('gc_value').value;
            document.getElementById('gc_price').value = val;
        }

        document.getElementById('giftCardForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Activating...';
            btn.disabled = true;

            const formData = new FormData(this);
            const payload = Object.fromEntries(formData.entries());

            fetch("{{ route('quick-sale.gift-card.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (giftCardModalInstance) giftCardModalInstance.hide();
                        showFloatingAlert('success', @json(__('Gift Card Activated: ')) + data.card_no);
                        window.LaravelDataTables?.['gift-card-table']?.ajax.reload();
                    } else {
                        showFloatingAlert('danger', data.message);
                    }
                })
                .catch(error => showFloatingAlert('danger', @json(__('Something went wrong'))))
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>
@endpush
