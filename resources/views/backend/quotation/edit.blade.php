@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Edit Quotation
@endsection

@push('css')
    <style>
        :root {
            --bg-body: #f3f4f6;
            --text-main: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --bg-card: #ffffff;
            --table-header: #f9fafb;
        }

        .erp-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin-bottom: 16px;
        }

        .erp-card-header {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 8px 8px 0 0;
        }

        .form-label-sm {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 4px;
            display: block;
        }

        .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control,
        .form-select {
            font-size: 0.875rem;
            border-color: var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
            height: 45px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            z-index: 5;
        }

        .search-input {
            padding-left: 36px;
            background-color: #f9fafb;
            border: 1px solid var(--border-color);
        }

        .search-results {
            position: absolute;
            background: #fff;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            top: 100%;
        }

        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .search-item:hover {
            background-color: #f0fdf9;
            color: var(--text-main);
        }

        .table-wrapper {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
        }

        .erp-table {
            margin-bottom: 0;
            width: 100%;
        }

        .erp-table thead th {
            background-color: var(--table-header);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .erp-table tbody td {
            padding: 0;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            height: 45px;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: none;
            padding: 8px 12px;
            background: transparent;
            font-size: 0.85rem;
            outline: none;
        }

        .cell-input:focus {
            background: #fff;
            box-shadow: inset 0 0 0 2px var(--primary-color);
        }

        .text-num {
            text-align: right;
        }

        .qty-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 5px;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--text-secondary);
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .btn-icon {
            color: #9ca3af;
            background: transparent;
            border: none;
            padding: 6px;
            border-radius: 4px;
        }

        .btn-icon:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover, #256e69);
            color: #fff;
        }

        .summary-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            margin-right: 30px;
        }

        .summary-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }

        .summary-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .layout-top-spacing {
            margin-top: 20px;
        }

        .quo-field-group .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }

        .quo-field-group .select2-container .select2-selection--single {
            height: 45px;
            display: flex;
            align-items: center;
        }

        .quo-field-group .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.4;
            padding-left: 12px;
        }

        .quo-field-group .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 43px;
        }

        /* Only flatten right corners when + button is present */
        .quo-field-group:has(.btn-quo-add) .select2-container .select2-selection--single,
        .quo-field-group:has(.btn-quo-add) > .form-select {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .quo-field-group .btn-quo-add {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            min-width: 42px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        @media (max-width: 575.98px) {
            .quo-field-group .btn-quo-add {
                min-width: 38px;
            }

            #addBranchModal .modal-dialog,
            #addEmployeeModal .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
        }

        .quo-form-error {
            display: none;
            border: 1px solid #f5c2c7;
            background: #f8d7da;
            color: #842029;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .quo-form-error.is-visible {
            display: block;
        }

        .quo-form-error-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .quo-form-error ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .quo-form-error li + li {
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Edit Quotation
        @endslot
        @slot('subtitle')
            Update quotation record: {{ $quotation->reference_no }}
        @endslot
        <li class="breadcrumb-item active">Edit Quotation</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <form action="{{ route('quotations.update', $quotation->id) }}" method="POST" enctype="multipart/form-data"
                id="quotation-form">
                @csrf
                @method('PUT')

                <div class="erp-card">
                    <div class="erp-card-header">
                        <i class="far fa-edit me-2 text-muted"></i> Basic Information
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Date <span class="required">*</span></label>
                                <input type="date" class="form-control" name="date"
                                    value="{{ old('date', $quotation->date) }}" required>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date"
                                    value="{{ old('expiry_date', $quotation->expiry_date) }}">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Reference No</label>
                                <input type="text" class="form-control" name="reference_no"
                                    value="{{ $quotation->reference_no }}" readonly>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Branch <span class="required">*</span></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="branch_id" id="branch_id" required>
                                        @foreach ($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ $quotation->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_branches')
                                        <button class="btn btn-outline-primary btn-quo-add" type="button"
                                            onclick="$('#addBranchModal').modal('show')" title="Add New Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Employee / Biller <span class="required">*</span></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="employee_id" id="employee_id" required>
                                        @foreach ($employees ?? [] as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ $quotation->seller_id == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_employees')
                                        <button class="btn btn-outline-primary btn-quo-add" type="button"
                                            onclick="openQuoEmployeeModal()" title="Add New Employee">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Customer <small class="text-muted">(Optional)</small></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="customer_id" id="customer_id">
                                        <option value="">Select customer...</option>
                                        @foreach ($customers ?? [] as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ $quotation->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_customers')
                                        <button class="btn btn-outline-primary btn-quo-add" type="button"
                                            onclick="$('#addCustomerModal').modal('show')" title="Add New Customer">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Supplier <small class="text-muted">(Optional)</small></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="supplier_id" id="supplier_id">
                                        <option value="">Select supplier...</option>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                {{ $quotation->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_suppliers')
                                        <button class="btn btn-outline-primary btn-quo-add" type="button"
                                            onclick="$('#addSupplierModal').modal('show')" title="Add New Supplier">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Payment Method</label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="account_id" id="account_id">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ $quotation->account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button class="btn btn-outline-primary btn-quo-add" type="button"
                                            onclick="openQuoAccountModal()" title="Add Payment Method">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Attach Document <small class="text-muted">(Leave blank to keep
                                        current)</small></label>
                                <input type="file" class="form-control" name="document">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="erp-card">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label-sm">Select Product (Scan Barcode or Type Name) <span
                                    class="required">*</span></label>
                            <div class="position-relative search-container">
                                <i class="fas fa-barcode search-icon"></i>
                                <input type="text" id="product_search" class="form-control search-input"
                                    placeholder="Type product code, name or scan barcode..." autocomplete="off">
                                <div class="search-results" id="search_results"></div>
                            </div>
                        </div>

                        <div class="table-wrapper mb-4">
                            <table class="table erp-table" id="quotation_table">
                                <thead>
                                    <tr>
                                        <th>Product Name / Code</th>
                                        <th class="text-center" style="width: 14%;">Quantity</th>
                                        <th class="text-num">Net Unit Price</th>
                                        <th class="text-num">Discount</th>
                                        <th class="text-num">Tax</th>
                                        <th class="text-num">SubTotal</th>
                                        <th class="text-center"><i class="fas fa-times"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="table_body">
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-5">
                                <label class="form-label-sm">Note / Remarks</label>
                                <textarea class="form-control" name="note" rows="3" placeholder="Add any notes here...">{{ $quotation->note }}</textarea>
                            </div>
                            <div class="col-lg-7">
                                <div class="row g-3 justify-content-end">
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Order Tax (%)</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="order_tax_rate" id="order_tax_rate" placeholder="0.00" value="{{ $quotation->order_tax_rate ?? 0 }}">
                                        <input type="hidden" name="order_tax" id="order_tax_value"
                                            value="{{ $quotation->order_tax }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Discount</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="order_discount" id="order_discount"
                                            value="{{ $quotation->order_discount }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Shipping Cost</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="shipping_cost" id="shipping_cost"
                                            value="{{ $quotation->shipping_cost }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-card mb-4">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="summary-item">
                            <span class="summary-label">Total Items</span>
                            <span class="summary-value" id="total-items">0 (0)</span>
                        </div>
                        <div class="summary-item border-start ps-4">
                            <span class="summary-label">Total Amount</span>
                            <span class="summary-value" id="total-amount">0.00</span>
                        </div>
                        <div class="summary-item border-start ps-4">
                            <span class="summary-label">Order Tax</span>
                            <span class="summary-value text-muted" id="summary-tax">0.00</span>
                        </div>
                        <div class="summary-item border-start ps-4">
                            <span class="summary-label">Discount</span>
                            <span class="summary-value text-danger" id="summary-discount">-0.00</span>
                        </div>
                        <div class="summary-item border-start ps-4">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value text-muted" id="summary-shipping">0.00</span>
                        </div>
                        <div class="summary-item border-start ps-4">
                            <span class="summary-label text-primary">Grand Total</span>
                            <span class="summary-value text-primary fs-4" id="grand-total">0.00</span>
                            <input type="hidden" name="grand_total" id="hidden_grand_total"
                                value="{{ $quotation->grand_total }}">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-submit shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Update Quotation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- IMEI Modal --}}
    <div class="modal fade" id="imeiModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-barcode me-2"></i><span id="modalProductTitle">Product Name (Code)</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="imeiModalForm">
                        <input type="hidden" id="currentRowId">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">Quantity</label>
                                <input type="number" class="form-control" id="modal_qty" min="1"
                                    value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">Unit Discount</label>
                                <input type="number" class="form-control" id="modal_discount" value="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">Net Unit Price</label>
                                <input type="number" class="form-control" id="modal_price" value="0.00">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Tax Amount</label>
                                <input type="number" class="form-control" id="modal_tax" value="0.00"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Product Unit</label>
                                <input type="text" class="form-control bg-light" id="modal_unit" readonly
                                    style="color: #111827 !important;">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label text-muted small fw-bold">IMEI or Serial Numbers</label>
                            <div id="imeiInputsArea" style="max-height: 250px; overflow-y: auto;"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" id="saveImeiBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    @include('backend.quotation.partials.quick_add')
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }

            $('#product_search').focus();

            // Product Custom Search
            $('#product_search').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('quotations.product_search') }}",
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
                                        <div class="fw-bold">${product.product_title}</div>
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

            // Add Product to Table with IMEI Logic
            window.addProductToTable = function(product) {
                let id = product.id;
                let variantId = product.variant_id ? product.variant_id : '';
                let uniqueRowId = 'product_' + id + (variantId ? '_' + variantId : '');

                if ($('#' + uniqueRowId).length > 0) {
                    let row = $('#' + uniqueRowId);
                    let qtyInput = row.find('.qty-input');
                    let currentQty = parseInt(qtyInput.val());
                    qtyInput.val(currentQty + 1).trigger('change');

                    if (product.has_imei == 1) {
                        updateImeiButtonState(row, currentQty + 1);
                        setTimeout(() => openImeiModalFunc(uniqueRowId), 200);
                    }
                } else {
                    // Edit Mode Values
                    let qty = product.existing_qty ? product.existing_qty : 1;
                    let price = parseFloat(product.price);
                    let discount = product.existing_discount ? parseFloat(product.existing_discount).toFixed(
                        2) : "0.00";
                    let taxRate = parseFloat(product.tax_rate) || 0;
                    let imeis = product.existing_imeis ? product.existing_imeis : "";

                    let imeiHtml = '',
                        imeiHiddenInput = '';
                    let unitName = product.unit ? product.unit.name : (product.unit_name ? product.unit_name :
                        'Piece');

                    if (product.has_imei == 1) {
                        let btnClass = imeis ? 'btn-success' : 'btn-info';
                        let btnText = imeis ? '<i class="fas fa-check"></i> IMEI Saved' :
                            '<i class="fas fa-barcode"></i> Add IMEI';

                        imeiHtml =
                            `<button type="button" class="btn btn-sm ${btnClass} text-white mt-1 open-imei-modal" data-row="${uniqueRowId}" style="font-size: 10px; padding: 2px 6px;">${btnText}</button>`;
                        imeiHiddenInput =
                            `<input type="hidden" name="imei_numbers[]" class="imei-data-input" value="${imeis}">`;
                    } else {
                        imeiHiddenInput = `<input type="hidden" name="imei_numbers[]" value="">`;
                    }

                    let newRow = `
                    <tr id="${uniqueRowId}" class="product-row">
                        <td class="px-3">
                            <div class="fw-semibold text-dark product-name">${product.product_title}</div>
                            <small class="text-muted product-code">${product.product_code}</small>
                            <input type="hidden" name="product_id[]" value="${id}">
                            <input type="hidden" name="variant_id[]" value="${variantId}">
                            <input type="hidden" class="row-unit" value="${unitName}">
                            ${imeiHiddenInput}
                            <div class="imei-btn-container">${imeiHtml}</div>
                        </td>
                        <td>
                            <div class="qty-container">
                                <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                                <input type="number" name="qty[]" class="cell-input text-center fw-bold qty-input" value="${qty}" min="1" style="width: 50px; min-width: 50px; max-width: 150px;">
                                <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                            </div>
                        </td>
                        <td><input type="number" step="any" name="net_unit_price[]" class="cell-input text-num price-input" value="${price.toFixed(2)}"></td>
                        <td><input type="number" step="any" name="discount[]" class="cell-input text-num text-danger discount-input" value="${discount}"></td>
                        <td>
                            <input type="number" step="any" name="tax[]" class="cell-input text-num text-muted tax-input" value="0.00" readonly>
                            <input type="hidden" class="tax-rate" name="tax_rate[]" value="${taxRate}">
                        </td>
                        <td class="px-3 text-num fw-bold subtotal">0.00</td>
                        <input type="hidden" name="total[]" class="subtotal-input" value="0">
                        <td class="text-center"><button type="button" class="btn-icon remove-row"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>`;

                    $('#table_body').append(newRow);
                    let $newRow = $('#' + uniqueRowId);
                    let $newQty = $newRow.find('.qty-input');
                    let newQtyLen = $newQty.val().toString().length;
                    $newQty.css('width', Math.max(50, newQtyLen * 12 + 20) + 'px');
                    calculateRow($newRow);
                }

                $('#search_results').hide();
                $('#product_search').val('').focus();
                calculateAll();
            };

            // ==========================================
            // IMEI Modal Logic
            // ==========================================
            window.openImeiModalFunc = function(rowId) {
                let row = $('#' + rowId);

                let name = row.find('.product-name').text();
                let code = row.find('.product-code').text();
                let qty = parseInt(row.find('.qty-input').val()) || 1;
                let price = parseFloat(row.find('.price-input').val()) || 0;
                let discount = parseFloat(row.find('.discount-input').val()) || 0;
                let tax = parseFloat(row.find('.tax-input').val()) || 0;
                let unit = row.find('.row-unit').val() || 'Piece';
                let savedImeis = row.find('.imei-data-input').val() ? row.find('.imei-data-input').val().split(
                    ',') : [];

                $('#currentRowId').val(rowId);
                $('#modalProductTitle').text(`${name} (${code})`);
                $('#modal_qty').val(qty);
                $('#modal_price').val(price.toFixed(2));
                $('#modal_discount').val(discount.toFixed(2));
                $('#modal_tax').val(tax.toFixed(2));
                $('#modal_unit').val(unit);

                generateImeiInputs(qty, savedImeis);
                $('#imeiModal').modal('show');
            };

            function generateImeiInputs(qty, savedValues = []) {
                let container = $('#imeiInputsArea');
                container.empty();
                for (let i = 0; i < qty; i++) {
                    let val = savedValues[i] || '';
                    container.append(
                        `<div class="mb-2"><input type="text" class="form-control modal-imei-field" placeholder="IMEI or Serial #${i + 1}" value="${val}"></div>`
                    );
                }
            }

            $('#modal_qty').on('input change', function() {
                let newQty = parseInt($(this).val()) || 1;
                if (newQty < 1) newQty = 1;
                let currentValues = [];
                $('.modal-imei-field').each(function() {
                    currentValues.push($(this).val());
                });
                generateImeiInputs(newQty, currentValues);
            });

            $(document).on('click', '.open-imei-modal', function() {
                openImeiModalFunc($(this).data('row'));
            });

            $('#saveImeiBtn').click(function() {
                let rowId = $('#currentRowId').val();
                let row = $('#' + rowId);

                let newQty = $('#modal_qty').val();
                let newPrice = $('#modal_price').val();
                let newDiscount = $('#modal_discount').val();
                let newTax = $('#modal_tax').val();

                let imeis = [];
                $('.modal-imei-field').each(function() {
                    let val = $(this).val().trim();
                    if (val) imeis.push(val);
                });

                let $qtyInput = row.find('.qty-input');
                $qtyInput.val(newQty);
                let qtyLen = String(newQty).length;
                $qtyInput.css('width', Math.max(50, qtyLen * 12 + 20) + 'px');
                row.find('.price-input').val(newPrice);
                row.find('.discount-input').val(newDiscount);
                row.find('.tax-input').val(newTax);

                row.find('.imei-data-input').val(imeis.join(','));

                let btn = row.find('.open-imei-modal');
                if (imeis.length === parseInt(newQty) && imeis.length > 0) {
                    btn.removeClass('btn-info btn-danger').addClass('btn-success').html(
                        '<i class="fas fa-check"></i> IMEI Saved');
                } else {
                    btn.removeClass('btn-success btn-info').addClass('btn-danger').html(
                        '<i class="fas fa-exclamation-triangle"></i> Fix IMEI');
                }

                calculateRow(row);
                calculateAll();
                $('#imeiModal').modal('hide');
            });

            function updateImeiButtonState(row, newQty) {
                let imeiInput = row.find('.imei-data-input');
                if (imeiInput.length > 0) {
                    let currentData = imeiInput.val() ? imeiInput.val().split(',') : [];
                    let btn = row.find('.open-imei-modal');
                    if (currentData.length !== newQty) {
                        btn.removeClass('btn-success btn-info').addClass('btn-danger').html(
                            '<i class="fas fa-exclamation-triangle"></i> Update IMEI');
                    } else {
                        btn.removeClass('btn-danger btn-info').addClass('btn-success').html(
                            '<i class="fas fa-check"></i> IMEI Saved');
                    }
                }
            }

            // ==========================================
            // Standard Table Calculations
            // ==========================================
            $(document).on('click', '.qty-btn', function() {
                let input = $(this).closest('.qty-container').find('.qty-input');
                let val = parseInt(input.val()) || 0;
                if ($(this).hasClass('plus')) val++;
                else if (val > 1) val--;
                input.val(val).trigger('change');
            });

            $(document).on('input change keyup', '.qty-input', function() {
                // Auto resize based on content (same as Purchase)
                let valLen = $(this).val().toString().length;
                let newWidth = Math.max(50, valLen * 12 + 20);
                $(this).css('width', newWidth + 'px');

                let row = $(this).closest('tr');
                updateImeiButtonState(row, parseInt($(this).val()) || 0);
                calculateRow(row);
                calculateAll();
            });

            $(document).on('change keyup', '.price-input, .discount-input', function() {
                calculateRow($(this).closest('tr'));
                calculateAll();
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateAll();
            });

            $('#order_tax_rate, #order_discount, #shipping_cost').on('change keyup', function() {
                calculateAll();
            });

            function calculateRow(row) {
                let qty = parseFloat(row.find('.qty-input').val()) || 0;
                let price = parseFloat(row.find('.price-input').val()) || 0;
                let discount = parseFloat(row.find('.discount-input').val()) || 0;
                let taxRate = parseFloat(row.find('.tax-rate').val()) || 0;

                let subtotalWithoutTax = (price * qty) - discount;
                let taxAmount = subtotalWithoutTax * (taxRate / 100);
                let finalSubtotal = subtotalWithoutTax + taxAmount;

                row.find('.tax-input').val(taxAmount.toFixed(2));
                row.find('.subtotal').text(finalSubtotal.toFixed(2));
                row.find('.subtotal-input').val(finalSubtotal.toFixed(2));
            }

            function calculateAll() {
                let totalQty = 0,
                    totalItems = 0,
                    totalAmount = 0;

                $('.product-row').each(function() {
                    totalItems++;
                    totalQty += parseFloat($(this).find('.qty-input').val()) || 0;
                    totalAmount += parseFloat($(this).find('.subtotal').text()) || 0;
                });

                let taxRate = parseFloat($('#order_tax_rate').val()) || 0;
                let orderTax = (totalAmount * taxRate) / 100;
                $('#order_tax_value').val(orderTax.toFixed(2));

                let orderDiscount = parseFloat($('#order_discount').val()) || 0;
                let shippingCost = parseFloat($('#shipping_cost').val()) || 0;
                let grandTotal = (totalAmount + orderTax + shippingCost) - orderDiscount;

                $('#total-items').text(`${totalItems} (${totalQty})`);
                $('#total-amount').text(totalAmount.toFixed(2));
                $('#summary-tax').text(orderTax.toFixed(2));
                $('#summary-discount').text('-' + orderDiscount.toFixed(2));
                $('#summary-shipping').text(shippingCost.toFixed(2));
                $('#grand-total').text(grandTotal.toFixed(2));
                $('#hidden_grand_total').val(grandTotal.toFixed(2));
            }

            // ==========================================
            // Load Existing Quotation Products on Page Load
            // ==========================================
            @foreach ($quotation->items as $item)
                addProductToTable({
                    id: "{{ $item->product_id }}",
                        product_title: {{ \Illuminate\Support\Js::from($item->product->product_title) }},
                    product_code: "{{ $item->product->product_code }}",
                    price: "{{ $item->net_unit_price }}",
                    variant_id: "{{ $item->variant_id }}",
                    tax_rate: "{{ $item->tax_rate }}",
                    has_imei: "{{ $item->product->has_imei }}",
                    unit_name: "{{ $item->product->unit->name ?? 'Piece' }}",
                    existing_qty: "{{ (int) $item->qty }}",
                    existing_discount: "{{ $item->discount }}",
                    existing_imeis: "{{ $item->imei_number }}"
                });
            @endforeach

            calculateAll();
        });
    </script>
@endpush
