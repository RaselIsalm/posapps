@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Inventory List
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <style>
        body {
            background-color: #f5f7fb;
        }

        .desc-collapsed {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .badge-stock-high {
            background-color: #1cc88a;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-stock-low {
            background-color: #e74a3b;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {
            .stat-card {
                margin-bottom: 10px;
            }

            .filter-container .col-md-2,
            .filter-container .col-md-4 {
                margin-bottom: 10px;
            }

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

            div.dataTables_wrapper div.dataTables_length {
                text-align: left;
            }

            .table-responsive {
                border: 0;
            }

            .table th,
            .table td {
                white-space: nowrap;
            }

            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: center !important;
                flex-wrap: wrap;
                margin-top: 15px;
            }

            div.dataTables_wrapper div.dataTables_info {
                text-align: center;
                white-space: normal;
                margin-bottom: 10px;
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
    Inventory Stocks
    @endslot
    @slot('subtitle')
    Manage and track all your purchase history
    @endslot
    <li class="breadcrumb-item active">Inventory</li>
    @endcomponent

    <div class="container-fluid py-4">
        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-start border-4 border-primary">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Total
                                Items</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_items']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-start border-4 border-danger">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger text-white me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Low Stock
                                Alerts</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['low_stock']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-start border-4 border-success">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success text-white me-3">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Total Stock
                                Value</small>
                            <h4 class="mb-0 fw-bold text-success">{{ \App\Helpers\CurrencyHelper::format($stats['total_value'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-start border-4 border-info">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info text-white me-3">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase"
                                style="font-size: 10px;">Branches</small>
                            <h4 class="mb-0 fw-bold">{{ \App\Helpers\CurrencyHelper::format($stats['total_branches'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">Branch</label>
                            <select id="branch_filter" class="form-select">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">Stock Type</label>
                            <select id="stock_filter" class="form-select">
                                <option value="">All Stock</option>
                                <option value="low" class="text-danger">Low Stock Alerts</option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="filter-label">Date Range</label>
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2">
                                <input type="date" id="start_date" class="form-control w-100">
                                <span class="text-muted fw-bold d-none d-sm-block">to</span>
                                <input type="date" id="end_date" class="form-control w-100">
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="button" id="filter_btn" class="btn btn-primary w-100 text-white">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <button type="button" id="reset_btn" class="btn btn-dark w-100 text-white">
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

    <div class="modal fade" id="viewStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg p-0 overflow-hidden">
                <div class="modal-body p-0" id="stock_details_content" style="font-family: 'Roboto', sans-serif; background: #fff; position: relative;">
                    <div class="d-flex justify-content-between align-items-center p-3 text-white rounded-top bg-primary">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-boxes me-2"></i>Stock Details (Code: <span id="v_code_header">...</span>)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="p-4">
                        {{-- Header Info --}}
                        <div class="row mb-4">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">Product Classification:
                                </h6>
                                <h5 class="fw-bold text-dark mb-1 font-monospace" id="v_code">...</h5>
                                <p class="mb-0 text-muted small">Category: <span id="v_category" class="badge" style="background-color: rgba(var(--primary-rgb, 48, 142, 135), 0.12); color: var(--primary-color, #308e87);"></span></p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">Supplier Info:
                                </h6>
                                <h5 class="fw-bold text-dark mb-1" id="v_supplier">...</h5>
                                <p class="mb-0 text-muted small">Last Restock: <span class="fw-bold" id="v_date"></span></p>
                            </div>
                        </div>

                        {{-- Meta --}}
                        <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded bg-light">
                            <div>
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">System Status</span>
                                <span class="fw-bold text-dark">Active Inventory</span>
                            </div>
                            <div class="text-center">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">Update Time</span>
                                <span class="fw-bold text-secondary">{{ date('d M, Y') }}</span>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">Stock Status</span>
                                <span class="badge bg-success px-3 py-1">IN STOCK</span>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="bg-light text-secondary">
                                    <tr style="font-size: 12px;">
                                        <th class="py-2" width="35%">PRODUCT NAME & VARIANT</th>
                                        <th class="py-2 text-center" width="15%">UNIT PRICE</th>
                                        <th class="py-2 text-center" width="15%">SELL PRICE</th>
                                        <th class="py-2 text-center" width="10%">QTY</th>
                                        <th class="py-2 text-end" width="25%">TOTAL VALUE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark d-block" id="v_name">...</span>
                                            <small class="text-muted">Variant: <span class="fw-bold text-dark" id="v_variant"></span></small>
                                        </td>
                                        <td class="text-center text-muted fw-bold" id="v_cost">0.00</td>
                                        <td class="text-center text-success fw-bold" id="v_price">0.00</td>
                                        <td class="text-center fw-bold fs-6 text-dark" id="v_qty">0</td>
                                        <td class="text-end fw-bold text-dark fs-6" id="v_total_value">0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-sm-7 mb-3 mb-sm-0">
                                <h6 class="text-uppercase text-secondary fw-bold mb-2" style="font-size: 12px; letter-spacing: 0.5px;">Description</h6>
                                <div class="p-2 rounded border-start border-3 mb-0" style="border-color: var(--primary-color, #308e87) !important; background-color: #f8f9fa;">
                                    <div class="text-dark small desc-collapsed" id="v_description">
                                        ...
                                    </div>
                                    <a href="javascript:void(0)" id="read_more_btn" class="small fw-bold text-primary mt-1 d-none" style="text-decoration: none;">Read More</a>
                                </div>
                            </div>
                            <div class="col-sm-5 text-end">
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px;">Grand Total Value</h6>
                                <h3 class="fw-bold mb-0" style="color: var(--primary-color, #308e87);" id="v_grand_total">0.00</h3>
                            </div>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                            <button type="button" class="btn btn-primary text-white px-4 fw-bold" id="btn_adjust_stock">
                                <i class="fas fa-sliders-h me-1"></i> Adjust Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-tools me-2"></i>Manual Stock Adjustment
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="adjustmentForm">
                    <div class="modal-body p-0">
                        <!-- Current Stock Info Header -->
                        <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark" id="adj_p_name">Product Name</h6>
                                <small class="text-muted" id="adj_p_variant">Variant Name</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Current
                                    Stock</small>
                                <span class="badge bg-white text-dark border shadow-sm fw-bold fs-6" id="adj_p_qty">0</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <input type="hidden" id="adj_id" name="id">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">Adjustment Type</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="fas fa-exchange-alt text-primary"></i></span>
                                        <select name="type" class="form-select shadow-none border-start-0" required
                                            style="height: 38px;">
                                            <option value="addition">Addition (+)</option>
                                            <option value="subtraction">Subtraction (-)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">Quantity</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="fas fa-layer-group text-primary"></i></span>
                                        <input type="number" step="0.01" name="amount"
                                            class="form-control shadow-none border-start-0" placeholder="0.00" required
                                            style="height: 38px;">
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label text-muted small fw-bold">Adjustment Reason</label>
                                    <div class="position-relative">
                                        <textarea name="reason" class="form-control shadow-none bg-white" rows="3"
                                            placeholder="Explain why you are adjusting this stock (e.g. Damage, Missing, Manual Correction)..."
                                            required style="border-radius: 10px;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 d-flex align-items-center text-white"
                            id="btn_save_adj">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                            <i class="fas fa-check-circle me-2"></i> Save Adjustment
                        </button>
                    </div>
                </form>
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
        function showCustomToast(message, type = 'success') {
            window.showAppToast(message, type);
        }

        $(document).ready(function () {
            const tableId = '#inventory-table';

            $(tableId).on('preXhr.dt', function (e, settings, data) {
                data.branch_id = $('#branch_filter').val();
                data.stock_filter = $('#stock_filter').val();
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
            });

            // Filter Button
            $('#filter_btn').on('click', function () {
                $(tableId).DataTable().ajax.reload();
            });

            // Reset Button
            $('#reset_btn').on('click', function () {
                $('#branch_filter, #stock_filter, #start_date, #end_date').val('');
                $(tableId).DataTable().ajax.reload();
            });

            $('body').on('click', tableId + ' tbody tr', function (e) {
                if ($(e.target).closest('button, a, .dropdown-menu, input, i').length > 0) {
                    return;
                }

                var id = $(this).attr('data-id');

                if (!id) return;

                $('#v_code, #v_name, #v_category, #v_variant, #v_supplier, #v_date, #v_description').text(@json(__('Loading...')));
                $('#v_cost, #v_price, #v_total_value, #v_grand_total').text('0.00');
                $('#v_qty').text('0').removeClass('text-danger').addClass('text-dark');

                $('#viewStockModal').modal('show');

                // AJAX Request
                $.ajax({
                    url: "{{ route('inventory.show', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        $('#v_code').text(data.product_code);
                        $('#v_code_header').text(data.product_code); // Update the header
                        $('#v_name').text(data.product_name);
                        $('#v_category').text(data.category);
                        $('#v_variant').text(data.variant);
                        $('#v_description').html(data.description || '<span class="text-muted">No description available</span>');
                        $('#v_cost').text(data.unit_price);
                        $('#v_price').text(data.selling_price);
                        $('#v_qty').text(data.quantity);
                        $('#v_supplier').text(data.supplier_name);
                        $('#v_date').text(data.last_restock);
                        $('#v_total_value').text(data.total_value);
                        $('#v_grand_total').text(data.total_value);

                        if (parseFloat(data.quantity) < 5) {
                            $('#v_qty').removeClass('text-dark').addClass('text-danger');
                        } else {
                            $('#v_qty').removeClass('text-danger').addClass('text-dark');
                        }

                        // Read More logic
                        var cleanText = $('<div>').html(data.description).text().trim();
                        $('#v_description').addClass('desc-collapsed');
                        
                        if (cleanText.length > 180) {
                            $('#read_more_btn').removeClass('d-none').text(@json(__('Read More')));
                            $('#read_more_btn').off('click').on('click', function() {
                                var desc = $('#v_description');
                                if (desc.hasClass('desc-collapsed')) {
                                    desc.removeClass('desc-collapsed');
                                    $(this).text(@json(__('Show Less')));
                                } else {
                                    desc.addClass('desc-collapsed');
                                    $(this).text(@json(__('Read More')));
                                }
                            });
                        } else {
                            $('#read_more_btn').addClass('d-none');
                        }
                    },
                    error: function (err) {
                        showCustomToast(@json(__('Error loading details')), 'error');
                        $('#viewStockModal').modal('hide');
                    }
                });
            });

            // Update row click to handle selected row for adjustment
            var currentStockId = null;
            $('body').on('click', tableId + ' tbody tr', function () {
                currentStockId = $(this).attr('data-id');
            });

            $('#btn_adjust_stock').on('click', function () {
                if (!currentStockId) return;

                // Get data from view modal before hiding it
                var pName = $('#v_name').text();
                var pVariant = $('#v_variant').text();
                var pQty = $('#v_qty').text();

                $('#adj_id').val(currentStockId);
                $('#adj_p_name').text(pName);
                $('#adj_p_variant').text(pVariant);
                $('#adj_p_qty').text(pQty);

                $('#viewStockModal').modal('hide');
                $('#adjustmentModal').modal('show');
            });

            // Submit Adjustment
            $('#adjustmentForm').on('submit', function (e) {
                e.preventDefault();
                var btn = $('#btn_save_adj');
                btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: "{{ route('inventory.adjust') }}",
                    type: "POST",
                    data: $(this).serialize() + "&_token={{ csrf_token() }}",
                    success: function (data) {
                        $(tableId).DataTable().ajax.reload(null, false);
                        
                        // Show toast strictly AFTER the modal is completely hidden
                        $('#adjustmentModal').one('hidden.bs.modal', function () {
                            showCustomToast(data.message, 'success');
                        }).modal('hide');
                    },
                    error: function (err) {
                        let msg = err.responseJSON ? err.responseJSON.message : @json(__('Something went wrong'));
                        showCustomToast(msg, 'error');
                    },
                    complete: function () {
                        btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                    }
                });
            });
        });
    </script>
@endpush