@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Edit Purchase
@endsection

@push('css')
    <style>
        .erp-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .erp-card-header {
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            background: #f9fafb;
            border-radius: 8px 8px 0 0;
        }

        .form-label-sm {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
            display: block;
        }

        .cell-input {
            border: none;
            width: 100%;
            text-align: center;
            outline: none;
            background: transparent;
            color: #111827;
            -webkit-text-fill-color: #111827;
        }

        .cell-input.tax-input.is-readonly {
            color: #4b5563;
            -webkit-text-fill-color: #4b5563;
            background-color: #f3f4f6;
        }

        .cell-input:focus {
            background: #fff;
            box-shadow: inset 0 0 0 1px var(--primary-color);
        }

        .text-num {
            text-align: right;
        }

        .search-results {
            position: absolute;
            background: #fff;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }

        .search-item:hover {
            background-color: #f0fdf9;
        }

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {
            .table-responsive .table th,
            .table-responsive .table td {
                white-space: nowrap;
            }
            .cell-input {
                min-width: 80px;
            }
            #display_grand_total {
                font-size: 1.2rem !important;
            }
            .col-md-5 .table td {
                white-space: nowrap;
            }
            .col-md-5 .table td:first-child {
                width: 50% !important;
            }
            .col-md-5 .table td:last-child {
                width: 50% !important;
            }
            .erp-card-header {
                padding: 10px 15px;
            }
            .card-body {
                padding: 15px !important;
            }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Edit Purchase
        @endslot
        @slot('subtitle')
            Update purchase record
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchase</a></li>
        <li class="breadcrumb-item active">Edit</li>
    @endcomponent

    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-12">
                <form action="{{ route('purchases.update', $purchase->id) }}" method="POST" id="purchase-form">
                    @csrf
                    @method('PUT')

                    <div class="erp-card">
                        <div class="erp-card-header"><i class="far fa-edit me-2"></i> Basic Information</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Date</label>
                                    <input type="date" class="form-control" name="date" value="{{ $purchase->date }}">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Memo No</label>
                                    <input type="text" class="form-control" name="memo_no"
                                        value="{{ $purchase->memo_no }}" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Branch</label>
                                    <div class="pur-bs-field-group">
                                        <select class="form-select" name="branch_id" id="branch_id">
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ $purchase->branch_id == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @can('create_branches')
                                            <button type="button" class="btn btn-outline-primary btn-pur-bs-add"
                                                onclick="openQuickBranchModal()" title="Add New Branch"
                                                aria-label="Add New Branch">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Supplier</label>
                                    <div class="pur-bs-field-group">
                                        <select class="form-select" name="supplier_id" id="supplier_id">
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}"
                                                    {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_suppliers')
                                            <button type="button" class="btn btn-outline-primary btn-pur-bs-add"
                                                onclick="openQuickSupplierModal()" title="Add New Supplier"
                                                aria-label="Add New Supplier">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>
                                            Received</option>
                                        <option value="pending" {{ $purchase->status == 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="ordered" {{ $purchase->status == 'ordered' ? 'selected' : '' }}>
                                            Ordered
                                        </option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Attach Document (Leave empty to keep existing)</label>
                                    <input type="file" class="form-control" name="document">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Currency</label>
                                    <div class="pur-field-group">
                                        <select class="form-select" name="currency" id="currency_selector">
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->code }}" data-exchange-rate="{{ $currency->exchange_rate }}" {{ $purchase->currency == $currency->code ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @can('create_currencies')
                                            <button type="button" class="btn btn-outline-primary btn-pur-add"
                                                onclick="openPurCurrencyModal()" title="Add New Currency">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label-sm">Exchange Rate</label>
                                    <input type="number" step="any" class="form-control" name="exchange_rate"
                                        id="exchange_rate" value="{{ $purchase->exchange_rate ?? 1.00 }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-card">
                        <div class="card-body p-4">
                            <div class="mb-4 position-relative">
                                <label class="form-label-sm">Search Product</label>
                                <input type="text" id="product_search" class="form-control"
                                    placeholder="Type product code or name..." autocomplete="off">
                                <div id="search_results" class="search-results"></div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product Name</th>
                                            <th width="10%">Qty</th>
                                            <th width="10%">Unit Cost</th>
                                            <th width="10%">Discount</th>
                                            <th width="10%">Tax</th>
                                            <th width="12%">Subtotal</th>
                                            <th width="5%"><i class="fas fa-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_body">
                                        @php
                                            $ex = $purchase->exchange_rate > 0 ? (float) $purchase->exchange_rate : 1;
                                        @endphp
                                        @foreach ($purchase->details as $detail)
                                            <tr class="product-row"
                                                id="product_{{ $detail->product_id }}_{{ $detail->variant_id }}">
                                                <td>
                                                    <div class="fw-bold">{{ $detail->product->product_title }}</div>
                                                    <small class="text-muted">{{ $detail->product->product_code }}</small>
                                                    @if ($detail->variant)
                                                        <br><small
                                                            class="text-primary">({{ $detail->variant->name ?? $detail->variant->variant_code }})</small>
                                                    @endif
                                                    <input type="hidden" name="product_id[]"
                                                        value="{{ $detail->product_id }}">
                                                    <input type="hidden" name="variant_id[]"
                                                        value="{{ $detail->variant_id }}">

                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-sm btn-info text-white"
                                                            onclick="openEditModal('product_{{ $detail->product_id }}_{{ $detail->variant_id }}', '{{ $detail->product_id }}', '{{ $detail->product->has_imei ?? 0 }}', {{ \Illuminate\Support\Js::from($detail->product->product_title) }}, '{{ $detail->product->tax_method ?? 'no_tax' }}')">
                                                            <i class="fas fa-edit"></i> Add/Edit Details
                                                        </button>
                                                    </div>

                                                    <div class="imei-display-area mt-1">
                                                        @php
                                                            $savedImeis = \App\Models\ProductImei::where(
                                                                'purchase_id',
                                                                $purchase->id,
                                                            )
                                                                ->where('product_id', $detail->product_id)
                                                                ->pluck('imei_number')
                                                                ->toArray();
                                                        @endphp

                                                        @if (count($savedImeis) > 0)
                                                            <div class="imei-hidden-container mt-1">
                                                                @foreach ($savedImeis as $imei)
                                                                    <input type="hidden"
                                                                        name="imei_number[{{ $detail->product_id }}][]"
                                                                        class="imei-hidden-input"
                                                                        value="{{ $imei }}">
                                                                    <span class="badge bg-info text-white me-1 mb-1"><i
                                                                            class="fas fa-barcode"></i>
                                                                        {{ $imei }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="qty[]"
                                                        class="cell-input qty-input fw-bold" value="{{ $detail->qty }}"
                                                        min="1" style="width: 50px; min-width: 50px; max-width: 150px;">
                                                </td>
                                                <td>
                                                    <input type="number" name="net_unit_cost[]"
                                                        class="cell-input cost-input" value="{{ $detail->net_unit_cost / $ex }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="discount[]"
                                                        class="cell-input discount-input text-danger"
                                                        value="{{ $detail->discount / $ex }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="tax[]"
                                                        class="cell-input tax-input {{ ($detail->product->tax_method ?? 'no_tax') === 'no_tax' ? 'is-readonly' : '' }}"
                                                        value="{{ $detail->tax / $ex }}" 
                                                        {{ ($detail->product->tax_method ?? 'no_tax') === 'no_tax' ? 'readonly' : '' }}>
                                                </td>
                                                <td class="subtotal fw-bold text-end">
                                                    {{ \App\Helpers\CurrencyHelper::format($detail->total / $ex, 2) }}</td>
                                                <td class="text-center"><button type="button"
                                                        class="btn btn-sm btn-danger remove-row"><i
                                                            class="fas fa-trash"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="row mt-4 g-4">
                                    <div class="col-lg-4">
                                        <label class="form-label-sm">Note / Remarks</label>
                                        <textarea class="form-control" name="note" rows="3" placeholder="Add any additional notes here...">{{ $purchase->note ?? '' }}</textarea>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="row g-3">
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <label class="form-label-sm">Payment Method</label>
                                                <div class="input-group flex-nowrap pur-field-group">
                                                    <select name="payment_method" id="pur_payment_method_edit" class="form-control" title="Payment account">
                                                        @forelse ($accounts as $account)
                                                            <option value="{{ $account->id }}" {{ (string) $purchase->payment_method === (string) $account->id || (string) $purchase->payment_method === (string) $account->name ? 'selected' : '' }}>{{ $account->name }}</option>
                                                        @empty
                                                            <option value="" disabled selected>No payment methods found</option>
                                                        @endforelse
                                                    </select>
                                                    @can('create_accounts')
                                                        <button class="btn btn-outline-primary btn-pur-add" type="button"
                                                            onclick="openPurAccountModal()" title="Add Payment Method"
                                                            aria-label="Add Payment Method">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-2">
                                                <label class="form-label-sm">Paid Amount</label>
                                                <input type="number" class="form-control text-end" name="paid_amount"
                                                    id="paid_amount" placeholder="0.00" value="{{ ($purchase->paid_amount ?? 0) / $ex }}" step="0.01" min="0" readonly>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label-sm">Order Tax Amount</label>
                                                <input type="number" class="form-control text-end" name="order_tax"
                                                    id="order_tax" placeholder="0.00" value="{{ ($purchase->order_tax ?? 0) / $ex }}">
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label-sm">Discount</label>
                                                <input type="number" class="form-control text-end" name="order_discount"
                                                    id="order_discount" placeholder="0.00"
                                                    value="{{ ($purchase->order_discount ?? 0.0) / $ex }}">
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-2">
                                                <label class="form-label-sm">Shipping Cost</label>
                                                <input type="number" class="form-control text-end" name="shipping_cost"
                                                    id="shipping_cost" placeholder="0.00"
                                                    value="{{ ($purchase->shipping_cost ?? 0.0) / $ex }}">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                                            <div class="fw-bold fs-5 me-3">Grand Total:</div>
                                            <div class="fw-bold fs-4 text-primary">
                                                {{ \App\Helpers\CurrencyHelper::getSymbol() }}<span id="display_grand_total">{{ $purchase->grand_total / $ex }}</span>
                                                <input type="hidden" name="grand_total" id="hidden_grand_total"
                                                    value="{{ $purchase->grand_total / $ex }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold"><i
                                        class="fas fa-save me-2"></i> Update Purchase</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Product Edit & IMEI Modal --}}
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i><span id="editProductTitle">Product Name</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_row_id">
                    <input type="hidden" id="modal_product_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-sm">Quantity</label>
                            <input type="number" id="modal_qty" class="form-control fw-bold" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Unit Discount</label>
                            <input type="number" id="modal_discount" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Tax Amount</label>
                            <input type="number" id="modal_tax" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-sm">Unit Cost</label>
                            <input type="number" id="modal_cost" class="form-control fw-bold">
                        </div>
                    </div>

                    <div class="mt-4" id="imei_section" style="display: none;">
                        <label class="form-label-sm text-primary"><i class="fas fa-barcode me-1"></i> IMEI or Serial
                            Numbers</label>
                        <div id="imei_inputs_container" class="mt-2">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom">
                    <button type="button" class="btn btn-primary px-4 fw-bold" onclick="updateProductRow()"><i
                            class="fas fa-check-circle me-1"></i> Update</button>
                </div>
            </div>
        </div>
    </div>

    @include('backend.purchase.partials.quick_add_account', ['paymentMethodSelectId' => 'pur_payment_method_edit'])
    @include('backend.purchase.partials.quick_add_currency', ['currencySelectId' => 'currency_selector'])
    @include('backend.purchase.partials.quick_add_branch_supplier')
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Update exchange rate when currency changes
            $('#currency_selector').on('change', function() {
                let rate = $(this).find(':selected').data('exchange-rate') || 1.00;
                $('#exchange_rate').val(rate);
            });
            // Trigger change on load to set initial rate
            $('#currency_selector').trigger('change');
            
            // Trigger auto resize on load for existing inputs
            setTimeout(function() {
                $('.qty-input').trigger('change');
            }, 500);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            // Initial calculation on page load
            calculateAll();

            // =====================================================
            // AJAX Product Search
            // =====================================================
            $('#product_search').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('purchases.product_search') }}",
                        type: "GET",
                        data: {
                            search: query
                        },
                        success: function(data) {
                            let searchResults = $('#search_results');
                            searchResults.empty(); // Clear previous results

                            if (data.length > 0) {
                                data.forEach(product => {
                                    // Create element via jQuery to avoid quotation syntax errors
                                    let item = $(`
                                        <div class="search-item">
                                            <div class="fw-bold">${product.product_title}</div>
                                            <small class="text-muted">Code: ${product.product_code} | Cost: ${product.product_cost || 0}</small>
                                        </div>
                                    `);

                                    // Safely bind the click event
                                    item.on('click', function() {
                                        addProductToTable(product);
                                    });

                                    searchResults.append(item);
                                });
                                searchResults.show();
                            } else {
                                searchResults.hide();
                            }
                        }
                    });
                } else {
                    $('#search_results').hide();
                }
            });

            // Hide search results when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#product_search').length) {
                    $('#search_results').hide();
                }
            });

            // Remove row from table
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateAll();
            });

            // Remove row from table
            $(document).on('input change keyup', '.qty-input', function() {
                // Auto resize based on content
                let valLen = $(this).val().toString().length;
                let newWidth = Math.max(50, valLen * 12 + 20);
                $(this).css('width', newWidth + 'px');
                
                let row = $(this).closest('tr');
                calculateRow(row);
                calculateAll();
            });

            // =====================================================
            // Calculation Functions
            // =====================================================
            function calculateRow(row) {
                let qty = parseFloat(row.find('.qty-input').val()) || 0;
                let cost = parseFloat(row.find('.cost-input').val()) || 0;
                let disc = parseFloat(row.find('.discount-input').val()) || 0;
                let tax = parseFloat(row.find('.tax-input').val()) || 0;

                let total = (cost * qty) + tax - disc;
                row.find('.subtotal').text(total.toFixed(2));
            }

            function calculateAll() {
                let subtotal = 0;
                $('.subtotal').each(function() {
                    subtotal += parseFloat($(this).text()) || 0;
                });

                let orderTax = parseFloat($('#order_tax').val()) || 0;
                let discount = parseFloat($('#order_discount').val()) || 0;
                let shipping = parseFloat($('#shipping_cost').val()) || 0;

                let grandTotal = (subtotal + orderTax + shipping) - discount;

                // Update UI and hidden inputs
                $('#display_grand_total').text(grandTotal.toFixed(2));
                $('#hidden_grand_total').val(grandTotal.toFixed(2));
            }

            // Recalculate when summary fields change
            $('#order_tax, #order_discount, #shipping_cost').on('input change', function() {
                calculateAll();
            });
            
            // =====================================================
            // Add Product To Table Function
            // =====================================================
            window.addProductToTable = function(product) {
                let id = product.id;
                let variantId = product.variant_id ? product.variant_id : '';
                let uniqueId = `product_${id}_${variantId}`;

                if ($('#' + uniqueId).length > 0) {
                    // If product exists, just increment quantity
                    let qtyInput = $('#' + uniqueId).find('.qty-input');
                    qtyInput.val(parseInt(qtyInput.val()) + 1);
                    calculateRow($('#' + uniqueId));
                } else {
                    let safeTitle = product.product_title ? product.product_title.replace(/'/g, "\\'") : '';
                    let cost = product.product_cost || 0;
                    let hasImei = product.has_imei || 0;

                    let newRow = `
                    <tr class="product-row" id="${uniqueId}">
                        <td>
                            <div class="fw-bold">${product.product_title}</div>
                            <small class="text-muted">${product.product_code}</small>
                            <input type="hidden" name="product_id[]" value="${id}">
                            <input type="hidden" name="variant_id[]" value="${variantId}">
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-info text-white" 
                                    onclick="openEditModal('${uniqueId}', '${id}', '${hasImei}', '${safeTitle}', '${product.tax_method || 'no_tax'}')">
                                    <i class="fas fa-edit"></i> Add/Edit Details
                                </button>
                            </div>
                            <div class="imei-display-area mt-1"></div>
                        </td>
                        <td><input type="number" name="qty[]" class="cell-input qty-input fw-bold" value="1" min="1" readonly style="width: 50px; min-width: 50px; max-width: 150px;"></td>
                        <td><input type="number" name="net_unit_cost[]" class="cell-input cost-input text-dark" value="${cost}" readonly></td>
                        <td><input type="number" name="discount[]" class="cell-input discount-input text-danger" value="0.00" readonly></td>
                        <td><input type="number" name="tax[]" class="cell-input tax-input ${product.tax_method === 'no_tax' || !product.tax_method ? 'is-readonly' : ''}" value="0.00" ${product.tax_method === 'no_tax' || !product.tax_method ? 'readonly' : ''}></td>
                        <td class="subtotal fw-bold text-end">${parseFloat(cost).toFixed(2)}</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>`;

                    $('#table_body').append(newRow);
                }

                calculateAll();
                $('#search_results').hide();
                $('#product_search').val('');
            };

            // =====================================================
            // Modal Functions
            // =====================================================
            window.openEditModal = function(rowId, productId, hasImei, title, taxMethod = 'no_tax') {
                let row = $('#' + rowId);
                $('#modal_row_id').val(rowId);
                $('#modal_product_id').val(productId);

                $('#editProductTitle').text(title);
                $('#modal_qty').val(row.find('.qty-input').val());
                $('#modal_cost').val(row.find('.cost-input').val());
                $('#modal_discount').val(row.find('.discount-input').val());
                
                $('#modal_tax').val(row.find('.tax-input').val());
                if (taxMethod === 'no_tax') {
                    $('#modal_tax').prop('readonly', true).addClass('is-readonly').css({ color: '#4b5563', backgroundColor: '#f3f4f6' });
                    $('#modal_tax').val('0.00'); // Force 0 if no tax
                } else {
                    $('#modal_tax').prop('readonly', false).removeClass('is-readonly').css({ color: '', backgroundColor: '' });
                }

                // Show/Hide IMEI section based on product type
                if (hasImei == 1 || hasImei == '1') {
                    $('#imei_section').show();
                    generateImeiInputs($('#modal_qty').val(), row);
                } else {
                    $('#imei_section').hide();
                }

                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            };

            // Re-generate IMEI fields dynamically if quantity changes inside modal
            $('#modal_qty').on('input', function() {
                if ($('#imei_section').is(':visible')) {
                    generateImeiInputs($(this).val(), $('#' + $('#modal_row_id').val()));
                }
            });

            function generateImeiInputs(qty, row) {
                let container = $('#imei_inputs_container');
                let existingImeis = [];

                row.find('.imei-hidden-input').each(function() {
                    existingImeis.push($(this).val());
                });

                container.empty();
                qty = parseInt(qty) || 0;

                for (let i = 0; i < qty; i++) {
                    let val = existingImeis[i] ? existingImeis[i] : '';
                    container.append(`
                        <input type="text" class="form-control mb-2 modal-imei-input border-primary" 
                        placeholder="IMEI or Serial #${i+1}" value="${val}">
                    `);
                }
            }

            // Save modal data back to the main table row
            window.updateProductRow = function() {
                let rowId = $('#modal_row_id').val();
                let productId = $('#modal_product_id').val();
                let row = $('#' + rowId);

                row.find('.qty-input').val($('#modal_qty').val());
                row.find('.cost-input').val($('#modal_cost').val());
                row.find('.discount-input').val($('#modal_discount').val());
                row.find('.tax-input').val($('#modal_tax').val());

                // Handle IMEIs
                if ($('#imei_section').is(':visible')) {
                    row.find('.imei-hidden-container').remove();

                    let imeiContainer = $('<div class="imei-hidden-container mt-1"></div>');
                    let imeiBadges = '';

                    $('.modal-imei-input').each(function() {
                        let val = $(this).val();
                        if (val !== '') {
                            imeiContainer.append(
                                `<input type="hidden" name="imei_number[${productId}][]" class="imei-hidden-input" value="${val}">`
                            );
                            imeiBadges +=
                                `<span class="badge bg-info text-white me-1 mb-1"><i class="fas fa-barcode"></i> ${val}</span>`;
                        }
                    });

                    row.find('.imei-display-area').html(imeiBadges);
                    row.find('td:first').append(imeiContainer);
                }

                calculateRow(row);
                calculateAll();

                // Hide modal instance safely
                let modalEl = document.getElementById('editProductModal');
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            };

        });
    </script>
@endpush
