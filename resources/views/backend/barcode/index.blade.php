@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Print Barcode
@endsection

@push('css')
    <style>
        :root {
            --bg-body: #f3f4f6;
            --text-main: #111827;
            --border-color: #e5e7eb;
        }

        .erp-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .erp-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .search-container {
            position: relative;
            width: 100%;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            z-index: 2;
            pointer-events: none;
        }

        .search-input {
            padding-left: 40px;
            height: 50px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #f9fafb;
            position: relative;
            z-index: 1;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .search-results {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            width: 100%;
            background: #fff;
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            z-index: 1050;
            display: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .search-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            line-height: 1.35;
        }

        .search-item:last-child {
            border-bottom: 0;
        }

        .search-item:hover {
            background-color: #f0fdf9;
        }

        .barcode-search-body {
            overflow: visible;
        }

        .table-wrapper {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .erp-table thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 12px 15px;
        }

        .erp-table tbody td {
            padding: 10px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .settings-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-generate {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            font-size: 1.1rem;
            width: 100%;
            transition: 0.3s;
        }

        .btn-generate:hover {
            background-color: var(--primary-hover, #256e69);
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .table-wrapper {
                margin: 0 !important;
                border: none !important;
            }

            .table-responsive {
                overflow-x: visible !important;
            }

            .erp-table thead {
                display: none;
            }

            .erp-table tbody tr {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                margin-bottom: 15px;
                padding: 15px;
                background: #fff;
            }

            .erp-table tbody tr#empty_row {
                display: block;
                border: 1px dashed var(--border-color);
                background: #f9fafb;
                padding: 30px 15px;
            }

            .erp-table tbody tr#empty_row td {
                padding: 0;
            }

            .erp-table tbody td {
                border: none !important;
                padding: 0 !important;
            }

            .erp-table tbody td:nth-child(1) {
                width: 100%;
                margin-bottom: 15px;
                padding-bottom: 15px !important;
                border-bottom: 1px solid #f3f4f6 !important;
            }

            .erp-table tbody td:nth-child(2) {
                width: 70%;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .erp-table tbody td:nth-child(2)::before {
                content: "Qty:";
                font-weight: 600;
                color: #6b7280;
                font-size: 14px;
            }

            .erp-table tbody td:nth-child(3) {
                width: 30%;
                text-align: right !important;
            }

            .erp-table tbody td:nth-child(3) .btn {
                background-color: #fee2e2 !important;
                color: #ef4444 !important;
                border: none;
                height: 38px;
                width: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
            }

            .search-input {
                font-size: 14px;
            }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Print Barcode
        @endslot
        @slot('subtitle')
            Generate and print product barcode labels
        @endslot
        <li class="breadcrumb-item active">Print Barcode</li>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <form action="{{ route('barcode.generate') }}" method="POST" target="_blank" id="barcodeForm">
                @csrf

                <div class="erp-card">
                    <div class="erp-card-header text-primary">
                        <i class="fas fa-barcode me-2"></i> Add Products to Print
                    </div>
                    <div class="card-body p-4 barcode-search-body">
                        <div class="search-container mb-2">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="product_search" class="form-control search-input w-100"
                                placeholder="Type product name or scan barcode..." autocomplete="off">
                            <div class="search-results" id="search_results"></div>
                        </div>
                    </div>
                </div>

                <div class="erp-card">
                    <div class="card-body p-0">
                        <div class="table-wrapper table-responsive m-md-4 m-2">
                            <table class="table erp-table mb-0" id="barcode_table">
                                <thead>
                                    <tr>
                                        <th>Product Name & Code</th>
                                        <th class="text-center" style="width: 200px;">Quantity (Stickers)</th>
                                        <th class="text-center" style="width: 80px;"><i class="fas fa-times"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="table_body">
                                    <tr id="empty_row">
                                        <td colspan="3" class="text-center text-muted py-4">No products added yet. Search
                                            above to add.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="erp-card">
                            <div class="erp-card-header"><i class="fas fa-cogs me-2 text-muted"></i> Print Settings (What to
                                show on sticker)</div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_company"
                                                id="show_company" checked value="1">
                                            <label class="form-check-label fw-bold ms-2" for="show_company">Show Company
                                                Name</label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_product_name"
                                                id="show_product_name" checked value="1">
                                            <label class="form-check-label fw-bold ms-2" for="show_product_name">Show
                                                Product Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_price"
                                                id="show_price" checked value="1">
                                            <label class="form-check-label fw-bold ms-2" for="show_price">Show Price</label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_product_code"
                                                id="show_product_code" checked value="1">
                                            <label class="form-check-label fw-bold ms-2" for="show_product_code">Show
                                                Product Code (Text)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="erp-card">
                            <div class="erp-card-header"><i class="fas fa-print me-2 text-muted"></i> Paper Size</div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <select class="form-select fw-bold" name="paper_size" id="paper_size"
                                        style="height: 50px;">
                                        <option value="38x25">Thermal Roll (38mm x 25mm)</option>
                                        <option value="40x30">Thermal Roll (40mm x 30mm)</option>
                                        <option value="50x25">Thermal Roll (50mm x 25mm)</option>
                                    </select>
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle"></i> Select the
                                        sticker size of your barcode printer.</small>
                                </div>
                                <button type="submit" class="btn btn-generate shadow-sm" id="btnSubmit">
                                    <i class="fas fa-print me-2"></i> Generate Barcodes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#product_search').focus();

            // AJAX Product Search
            $('#product_search').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('barcode.search_product') }}",
                        type: "GET",
                        data: {
                            search: query
                        },
                        success: function(data) {
                            let html = '';
                            if (data.length > 0) {
                                data.forEach(product => {
                                    let productData = JSON.stringify(product).replace(
                                        /'/g, "&#39;");
                                    html += `
                                <div class="search-item" onclick='addProductToTable(${productData})'>
                                    <div class="fw-bold text-dark">${product.product_title}</div>
                                    <small class="text-muted">Code: ${product.product_code} | Price: ${parseFloat(product.price).toFixed(2)}</small>
                                </div>`;
                                });
                                $('#search_results').html(html).show();
                            } else {
                                $('#search_results').hide();
                            }
                        }
                    });
                } else {
                    $('#search_results').hide();
                }
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('.search-container').length) $('#search_results').hide();
            });

            // Add Product to Table
            window.addProductToTable = function(product) {
                $('#empty_row').hide();
                let variantId = product.variant_id ? product.variant_id : '';
                let uniqueRowId = 'row_' + product.id + '_' + variantId;

                // If product already in table, increase quantity by 10
                if ($('#' + uniqueRowId).length > 0) {
                    let qtyInput = $('#' + uniqueRowId).find('.qty-input');
                    qtyInput.val(parseInt(qtyInput.val()) + 10);
                } else {
                    let newRow = `
                <tr id="${uniqueRowId}" class="product-row">
                    <td>
                        <div class="fw-bold text-dark">${product.product_title}</div>
                        <small class="text-muted">${product.product_code}</small>
                        <input type="hidden" name="product_id[]" value="${product.id}">
                        <input type="hidden" name="variant_id[]" value="${variantId}">
                        <input type="hidden" name="product_code[]" value="${product.product_code}">
                        <input type="hidden" name="product_name[]" value="${product.product_title}">
                        <input type="hidden" name="price[]" value="${product.price}">
                    </td>
                    <td>
                        <input type="number" name="qty[]" class="form-control text-center fw-bold qty-input" value="10" min="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-light text-danger remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>`;
                    $('#table_body').append(newRow);
                }

                $('#search_results').hide();
                $('#product_search').val('').focus();
            };

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                if ($('.product-row').length === 0) {
                    $('#empty_row').show();
                }
            });

            // Form Validation before submit
            $('#barcodeForm').on('submit', function(e) {
                if ($('.product-row').length === 0) {
                    e.preventDefault();
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Please add at least one product to generate barcodes.')));
                    } else {
                        alert(@json(__('Please add at least one product to generate barcodes.')));
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
