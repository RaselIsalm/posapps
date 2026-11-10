@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create Quotation
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

        /* Custom Search Box */
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

        /* Table Styles */
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

        /* Qty Buttons */
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

        /* Summary Card */
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

        @media (max-width: 767.98px) {
            .summary-card {
                flex-direction: column;
                align-items: stretch;
            }
            .summary-card .d-flex {
                flex-direction: column;
                align-items: stretch !important;
                width: 100%;
                gap: 0 !important;
            }
            .summary-item {
                margin-right: 0;
                padding-left: 0 !important;
                border-left: none !important;
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 10px;
                padding-top: 10px;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }
            .summary-item.border-start {
                border-left: none !important;
            }
            .summary-item:last-child {
                border-bottom: none;
            }
            .summary-card button {
                width: 100%;
                margin-top: 15px;
            }
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .erp-table {
                min-width: 800px;
            }
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
            Add Quotation
        @endslot
        @slot('subtitle')
            Create a new general quotation record
        @endslot
        <li class="breadcrumb-item active">Add Quotation</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <form action="{{ route('quotations.store') }}" method="POST" enctype="multipart/form-data" id="quotation-form">
                @csrf

                <div class="erp-card">
                    <div class="erp-card-header">
                        <i class="far fa-edit me-2 text-muted"></i> Basic Information
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Date <span class="required">*</span></label>
                                <input type="date" class="form-control" name="date"
                                    value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date"
                                    value="{{ old('expiry_date') }}">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Reference No</label>
                                <input type="text" class="form-control" name="reference_no"
                                    value="{{ $reference_no ?? 'QUO-AUTO' }}" readonly>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Branch <span class="required">*</span></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="branch_id" id="branch_id" required>
                                        <option selected disabled value="">Select branch...</option>
                                        @foreach ($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                                        <option selected disabled value="">Select employee...</option>
                                        @foreach ($employees ?? [] as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
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
                                        <option selected disabled value="">Select customer...</option>
                                        @foreach ($customers ?? [] as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
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
                                        <option selected disabled value="">Select supplier...</option>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
                                <label class="form-label-sm">Payment Method <span class="required">*</span></label>
                                <div class="input-group quo-field-group">
                                    <select class="form-select select2" name="account_id" id="account_id" required>
                                        <option selected disabled value="">Select payment method...</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
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
                                <label class="form-label-sm">Attach Document</label>
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
                                <textarea class="form-control" name="note" rows="3" placeholder="Add any notes here..."></textarea>
                            </div>
                            <div class="col-lg-7">
                                <div class="row g-3 justify-content-end">
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Order Tax (%)</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="order_tax_rate" id="order_tax_rate" placeholder="0.00" value="0">
                                        <input type="hidden" name="order_tax" id="order_tax_value" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Discount</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="order_discount" id="order_discount" placeholder="0.00" value="0.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Shipping Cost</label>
                                        <input type="number" step="any" class="form-control text-end"
                                            name="shipping_cost" id="shipping_cost" placeholder="0.00" value="0.00">
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
                            <input type="hidden" name="grand_total" id="hidden_grand_total" value="0">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-submit shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Submit Quotation
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

    {{-- Add Customer Modal --}}
    @can('create_customers')
        <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addCustomerForm" onsubmit="return saveQuoCustomer(event)">
                            @csrf
                            <div class="quo-form-error" id="quoCustomerFormError" role="alert"></div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Customer Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone *</label>
                                <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Email (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email">
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-1">Address (Optional)</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-1">Note (Optional)</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveCustomer">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Supplier Modal --}}
    @can('create_suppliers')
        <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Supplier</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addSupplierForm" onsubmit="return saveQuoSupplier(event)">
                            @csrf
                            <div class="quo-form-error" id="quoSupplierFormError" role="alert"></div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Supplier name">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Company Name</label>
                                <input type="text" name="company_name" class="form-control" placeholder="Company name">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone *</label>
                                <input type="text" name="phone" class="form-control" required placeholder="Phone number">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Address</label>
                                <input type="text" name="address" class="form-control" placeholder="Address">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Note</label>
                                <input type="text" name="note" class="form-control" placeholder="Note">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Previous Due</label>
                                <input type="number" name="previous_due" class="form-control" step="any" min="0"
                                    placeholder="0.00">
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_supplier_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_supplier_is_active">Active</label>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSupplier">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Branch Modal --}}
    @can('create_branches')
        <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addBranchForm" onsubmit="return saveQuoBranch(event)" enctype="multipart/form-data">
                            @csrf
                            <div class="quo-form-error" id="quoBranchFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Branch name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Manager *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="manager_id" id="quoBranchManagerSelect" class="form-control" required>
                                            <option value="">Select Manager</option>
                                            @foreach ($branchManagers ?? [] as $manager)
                                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('manage_users')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestManagerModal()" title="Add Manager">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Account *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="account_id" id="quoBranchAccountSelect" class="form-control" required>
                                            <option value="">Select Account</option>
                                            @foreach ($accounts ?? [] as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_accounts')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestAccountModal()" title="Add Account">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Address *</label>
                                    <input type="text" name="address" class="form-control" required placeholder="Branch address">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Phone (Mobile)</label>
                                    <input type="text" name="phone" class="form-control" placeholder="Mobile">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Telephone (Landline)</label>
                                    <input type="text" name="telephone" class="form-control" placeholder="Landline">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">BIN No</label>
                                    <input type="text" name="bin_number" class="form-control" placeholder="BIN number">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Website</label>
                                    <input type="text" name="website" class="form-control" placeholder="https://example.com">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Branch Logo</label>
                                    <input type="file" name="branch_logo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="quo_branch_is_active"
                                            name="is_active" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="quo_branch_is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBranch">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Employee Modal --}}
    @can('create_employees')
        <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Employee</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addEmployeeForm" onsubmit="return saveQuoEmployee(event)">
                            @csrf
                            <div class="quo-form-error" id="quoEmployeeFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Employee name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Email *</label>
                                    <input type="email" name="email" class="form-control" required placeholder="Login email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Phone *</label>
                                    <input type="tel" name="phone" class="form-control" required placeholder="Phone number">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Branch *</label>
                                    <select name="branch_id" id="quoEmployeeBranch" class="form-control" required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Department *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="department_id" id="quoEmployeeDepartmentSelect" class="form-control"
                                            required>
                                            <option value="">Select Department</option>
                                            @foreach ($departments ?? [] as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_departments')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestDepartmentModal()" title="Add Department">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Role *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="role" id="quoEmployeeRoleSelect" class="form-control" required>
                                            <option value="">Select Role</option>
                                            @foreach ($employeeRoles ?? [] as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('manage_roles')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestRoleModal()" title="Add Role">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Password *</label>
                                    <input type="password" name="password" class="form-control" required minlength="6"
                                        autocomplete="new-password" placeholder="Login password">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Confirm Password *</label>
                                    <input type="password" name="password_confirmation" class="form-control" required
                                        minlength="6" autocomplete="new-password" placeholder="Confirm password">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Salary *</label>
                                    <input type="number" name="salary" class="form-control" required step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Max Commission</label>
                                    <input type="number" name="max_commission" class="form-control" step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Sale Target</label>
                                    <input type="number" name="sale_target" class="form-control" step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Joining Date *</label>
                                    <input type="date" name="joining_date" class="form-control" required
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Leaving Date</label>
                                    <input type="date" name="leaving_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="quo_employee_status"
                                            name="status" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="quo_employee_status">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveEmployee">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Account / Payment Method Modal --}}
    @can('create_accounts')
        <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addAccountForm" onsubmit="return saveQuoAccount(event)">
                            @csrf
                            <div class="quo-form-error" id="quoAccountFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Account name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Type *</label>
                                    <select name="type" id="quo_account_type" class="form-control" required>
                                        <option value="cash" selected>Cash</option>
                                        <option value="bank">Bank</option>
                                        <option value="mobile banking">Mobile Banking</option>
                                        <option value="digital wallet">Digital Wallet</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">
                                        Account Number <span id="quo_account_number_required" class="text-danger d-none">*</span>
                                    </label>
                                    <input type="text" name="account_number" id="quo_account_number" class="form-control"
                                        placeholder="Not required for cash">
                                    <small id="quo_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Initial Balance</label>
                                    <input type="number" name="initial_balance" class="form-control" step="0.01" min="0"
                                        value="0" placeholder="0.00">
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Note</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="quo_account_is_active"
                                            name="is_active" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="quo_account_is_active">Yes</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="quo_account_is_default"
                                            name="is_default" style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="quo_account_is_default">Yes</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="quo_account_manual"
                                            name="allow_manual_amount" style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="quo_account_manual">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveAccount">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @include('backend.shared.partials.nested_branch_employee_quick_add', [
        'nx' => [
            'managerSelectId' => 'quoBranchManagerSelect',
            'accountSelectId' => 'quoBranchAccountSelect',
            'departmentSelectId' => 'quoEmployeeDepartmentSelect',
            'roleSelectId' => 'quoEmployeeRoleSelect',
            'branchParentModalId' => 'addBranchModal',
            'employeeParentModalId' => 'addEmployeeModal',
            'roles' => $employeeRoles ?? [],
        ],
    ])
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
                    let price = parseFloat(product.price);
                    let taxRate = parseFloat(product.tax_rate) || 0;

                    let imeiHtml = '',
                        imeiHiddenInput = '';
                    let unitName = product.unit ? product.unit.name : 'Piece';

                    if (product.has_imei == 1) {
                        imeiHtml =
                            `<button type="button" class="btn btn-sm btn-info text-white mt-1 open-imei-modal" data-row="${uniqueRowId}" style="font-size: 10px; padding: 2px 6px;"><i class="fas fa-barcode"></i> Add IMEI</button>`;
                        imeiHiddenInput =
                            `<input type="hidden" name="imei_numbers[]" class="imei-data-input" value="">`;
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
                                <input type="number" name="qty[]" class="cell-input text-center fw-bold qty-input" value="1" min="1" style="width: 50px; min-width: 50px; max-width: 150px;">
                                <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                            </div>
                        </td>
                        <td><input type="number" step="any" name="net_unit_price[]" class="cell-input text-num price-input" value="${price.toFixed(2)}"></td>
                        <td><input type="number" step="any" name="discount[]" class="cell-input text-num text-danger discount-input" value="0.00"></td>
                        <td>
                            <input type="number" step="any" name="tax[]" class="cell-input text-num text-muted tax-input" value="0.00" readonly>
                            <input type="hidden" class="tax-rate" name="tax_rate[]" value="${taxRate}">
                        </td>
                        <td class="px-3 text-num fw-bold subtotal">0.00</td>
                        <input type="hidden" name="total[]" class="subtotal-input" value="0">
                        <td class="text-center"><button type="button" class="btn-icon remove-row"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>`;

                    $('#table_body').append(newRow);
                    calculateRow($('#' + uniqueRowId));

                    if (product.has_imei == 1) {
                        setTimeout(() => openImeiModalFunc(uniqueRowId), 200);
                    }
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

            $(document).on('change keyup', '.qty-input', function() {
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
        });

        // Hard rule: Quotation quick-add (+ Branch/Employee/Customer/Supplier/Account)
        // must NEVER show browser alert / toastr / floating toast.
        (function () {
            if (window.__quoAlertGuardInstalled) return;
            window.__quoAlertGuardInstalled = true;
            window.__quoQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__quoQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            const nativeConfirm = window.confirm;
            window.confirm = function () {
                if (window.__quoQuickAddSilent) return true;
                return nativeConfirm.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__quoQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        })();

        function quoMutePopups(on) {
            window.__quoQuickAddSilent = !!on;
            if (typeof toastr !== 'undefined') {
                if (on) {
                    if (!window.__quoToastrBackup) {
                        window.__quoToastrBackup = {
                            success: toastr.success,
                            error: toastr.error,
                            info: toastr.info,
                            warning: toastr.warning
                        };
                    }
                    toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
                } else if (window.__quoToastrBackup) {
                    toastr.success = window.__quoToastrBackup.success;
                    toastr.error = window.__quoToastrBackup.error;
                    toastr.info = window.__quoToastrBackup.info;
                    toastr.warning = window.__quoToastrBackup.warning;
                }
            }
        }

        function quoParseJson(response) {
            if (typeof response === 'string') {
                try {
                    return JSON.parse(response);
                } catch (err) {
                    return null;
                }
            }
            return response;
        }

        function quoEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function quoShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;

            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message && typeof message === 'object') {
                Object.keys(message).forEach(function (key) {
                    const items = message[key];
                    if (Array.isArray(items)) {
                        items.forEach(function (item) { messages.push(item); });
                    } else if (items) {
                        messages.push(items);
                    }
                });
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }

            if (!messages.length) {
                messages = [@json(__('Something went wrong. Please try again.'))];
            }

            let html = '<div class="quo-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + quoEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + quoEscapeHtml(m) + '</li>';
                });
                html += '</ul>';
            }

            box.innerHTML = html;
            box.classList.add('is-visible');

            if (formEl) {
                try {
                    formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (err) {}
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function quoClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.quo-field-error').remove();
            }
        }

        function quoMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block quo-field-error';
                feedback.textContent = msg;
                if (input.parentElement && input.parentElement.classList.contains('form-check')) {
                    input.parentElement.insertAdjacentElement('afterend', feedback);
                } else {
                    input.insertAdjacentElement('afterend', feedback);
                }
            });
        }

        function quoExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (err) {
                    payload = null;
                }
            }

            if (xhr.status === 419) {
                return {
                    messages: [@json(__('Your session has expired. Please refresh the page and try again.'))],
                    errors: null
                };
            }
            if (xhr.status === 403) {
                return {
                    messages: [(payload && payload.message) || @json(__('You do not have permission to perform this action.'))],
                    errors: null
                };
            }
            if (xhr.status === 422 && payload && payload.errors) {
                const messages = [];
                Object.keys(payload.errors).forEach(function (key) {
                    (payload.errors[key] || []).forEach(function (item) {
                        messages.push(item);
                    });
                });
                return {
                    messages: messages.length ? messages : [@json(__('Validation failed.'))],
                    errors: payload.errors
                };
            }
            if (payload && payload.message) {
                return { messages: [payload.message], errors: payload.errors || null };
            }
            if (xhr.status === 0) {
                return { messages: [@json(__('Network error. Please check your connection.'))], errors: null };
            }
            return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
        }

        function quoHandleSaveFail(errorId, formEl, xhr) {
            const payload = quoExtractErrorPayload(xhr);
            quoClearFormError(errorId, formEl);
            if (payload.errors) {
                quoMarkFieldErrors(formEl, payload.errors);
            }
            quoShowFormError(errorId, payload.messages, formEl);
        }

        function quoAppendSelectOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const $sel = $(el);
            const value = String(id);
            const label = text || ('#' + value);

            if ($sel.find('option[value="' + value + '"]').length === 0) {
                $sel.append(new Option(label, value, false, false));
            }

            if (selectIt) {
                $sel.find('option[disabled]').prop('selected', false);
                $sel.val(value).trigger('change');
            }
        }

        function quoAppendPlainOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const value = String(id);
            const label = text || ('#' + value);
            if ($(el).find('option[value="' + value + '"]').length === 0) {
                el.appendChild(new Option(label, value, false, false));
            }
            if (selectIt) {
                el.value = value;
            }
        }

        function quoPostJson(url, data, isFormData) {
            quoMutePopups(true);
            const options = {
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };
            if (isFormData) {
                options.processData = false;
                options.contentType = false;
            }
            return $.ajax(options).always(function () {
                quoMutePopups(false);
            });
        }

        function saveQuoCustomer(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addCustomerForm');
            const btn = $('#btnSaveCustomer');
            if (!form.length) return false;

            quoClearFormError('quoCustomerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('customers.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.customer) {
                        quoAppendSelectOption('customer_id', response.customer.id, response.customer.name, true);
                        $('#addCustomerModal').modal('hide');
                        form[0].reset();
                        quoClearFormError('quoCustomerFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoCustomerFormError', (response && response.message) || @json(__('Could not save customer.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoCustomerFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function saveQuoSupplier(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addSupplierForm');
            const btn = $('#btnSaveSupplier');
            if (!form.length) return false;

            quoClearFormError('quoSupplierFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('suppliers.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.supplier) {
                        const s = response.supplier;
                        if (s.is_active === false) {
                            quoShowFormError('quoSupplierFormError', @json(__('Supplier saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_supplier_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('supplier_id', s.id, s.name, true);
                        $('#addSupplierModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_supplier_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoSupplierFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoSupplierFormError', (response && response.message) || @json(__('Could not save supplier.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoSupplierFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function saveQuoBranch(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addBranchForm');
            const btn = $('#btnSaveBranch');
            if (!form.length) return false;

            quoClearFormError('quoBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('branches.store') }}", new FormData(form[0]), true)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            quoShowFormError('quoBranchFormError', @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_branch_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('branch_id', b.id, b.name, true);
                        quoAppendPlainOption('quoEmployeeBranch', b.id, b.name, false);
                        $('#addBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_branch_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoBranchFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoBranchFormError', (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoBranchFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function openQuoEmployeeModal() {
            const form = document.getElementById('addEmployeeForm');
            if (!form) return;
            form.reset();
            quoClearFormError('quoEmployeeFormError', form);
            const status = document.getElementById('quo_employee_status');
            if (status) status.checked = true;
            const joining = form.querySelector('[name="joining_date"]');
            if (joining && !joining.value) {
                joining.value = new Date().toISOString().slice(0, 10);
            }
            const currentBranch = document.getElementById('branch_id')?.value || '';
            const empBranch = document.getElementById('quoEmployeeBranch');
            if (empBranch && currentBranch) {
                empBranch.value = currentBranch;
            }
            $('#addEmployeeModal').modal('show');
        }

        function saveQuoEmployee(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addEmployeeForm');
            const btn = $('#btnSaveEmployee');
            if (!form.length) return false;

            quoClearFormError('quoEmployeeFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('employees.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.employee) {
                        const emp = response.employee;
                        if (!emp.status) {
                            quoShowFormError('quoEmployeeFormError', @json(__('Employee saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const status = document.getElementById('quo_employee_status');
                            if (status) status.checked = true;
                            return;
                        }
                        quoAppendSelectOption('employee_id', emp.id, emp.name, true);
                        const branchSel = document.getElementById('branch_id');
                        if (emp.branch_id && branchSel && (!branchSel.value || branchSel.selectedOptions[0]?.disabled)) {
                            $(branchSel).val(String(emp.branch_id)).trigger('change');
                        }
                        $('#addEmployeeModal').modal('hide');
                        form[0].reset();
                        const status = document.getElementById('quo_employee_status');
                        if (status) status.checked = true;
                        quoClearFormError('quoEmployeeFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoEmployeeFormError', (response && response.message) || @json(__('Could not save employee.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoEmployeeFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function syncQuoAccountNumberRequirement() {
            const typeEl = document.getElementById('quo_account_type');
            const numberEl = document.getElementById('quo_account_number');
            const requiredMark = document.getElementById('quo_account_number_required');
            const hintEl = document.getElementById('quo_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        function openQuoAccountModal() {
            const form = document.getElementById('addAccountForm');
            if (!form) return;
            form.reset();
            quoClearFormError('quoAccountFormError', form);
            const active = document.getElementById('quo_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('quo_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncQuoAccountNumberRequirement();
            $('#addAccountModal').modal('show');
        }

        function saveQuoAccount(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addAccountForm');
            const btn = $('#btnSaveAccount');
            if (!form.length) return false;

            quoClearFormError('quoAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('accounts.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            quoShowFormError('quoAccountFormError', @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_account_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('account_id', a.id, a.name, true);
                        quoAppendPlainOption('quoBranchAccountSelect', a.id, a.name, false);
                        $('#addAccountModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_account_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoAccountFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoAccountFormError', (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoAccountFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        $(document).on('change', '#quo_account_type', syncQuoAccountNumberRequirement);
        $(document).on('input change', '#addCustomerForm input, #addCustomerForm select, #addCustomerForm textarea, #addSupplierForm input, #addSupplierForm select, #addSupplierForm textarea, #addBranchForm input, #addBranchForm select, #addBranchForm textarea, #addEmployeeForm input, #addEmployeeForm select, #addEmployeeForm textarea, #addAccountForm input, #addAccountForm select, #addAccountForm textarea', function () {
            $(this).removeClass('is-invalid');
            $(this).siblings('.quo-field-error').remove();
            $(this).closest('.form-check').next('.quo-field-error').remove();
        });
    </script>
@endpush
