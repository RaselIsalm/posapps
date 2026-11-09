@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Add Purchase
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

        .text-danger {
            font-size: 0.75rem;
            margin-top: 2px;
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
            color: #4b5563 !important;
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
            color: #111827 !important;
            -webkit-text-fill-color: #111827 !important;
        }

        .cell-input.tax-input.is-readonly {
            color: #4b5563 !important;
            -webkit-text-fill-color: #4b5563 !important;
            background-color: #f3f4f6 !important;
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

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {
            .table-wrapper {
                overflow-x: auto !important;
            }
            .erp-table th,
            .erp-table td {
                white-space: nowrap;
            }
            .cell-input {
                min-width: 80px;
            }
            .summary-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .summary-item {
                margin-right: 0;
                margin-bottom: 10px;
                border-left: none !important;
                padding-left: 0 !important;
            }
            .summary-item.border-start {
                border-left: none !important;
                padding-left: 0 !important;
            }
            .btn-submit {
                width: 100%;
                margin-top: 15px;
            }
        }

        .floating-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid #10b981;
            background: #fff;
            color: #333;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Add Purchase
        @endslot
        @slot('subtitle')
            Create a new purchase record
        @endslot
        <li class="breadcrumb-item active">Add Purchase</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('purchases.store') }}" method="POST" enctype="multipart/form-data" id="purchase-form">
                @csrf

                @if (isset($quotation))
                    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                @endif

                <div class="erp-card">
                    <div class="erp-card-header">
                        <i class="far fa-edit me-2 text-muted"></i> Basic Information
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Date <span class="required">*</span></label>
                                <input type="date" class="form-control" name="date" id="today"
                                    value="{{ old('date', date('Y-m-d')) }}">
                                @error('date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Memo No</label>
                                <input type="text" class="form-control" name="memo_no"
                                    placeholder="PO-{{ date('Y') }}-XXXX" value="{{ old('memo_no') }}">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Branch <span class="required">*</span></label>
                                <div class="d-flex align-items-center gap-1">
                                    <select class="form-select" name="branch_id" id="branch_id" required style="flex: 1;">
                                        <option value="" selected disabled>Select branch...</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickBranchModal()" title="Add New Branch" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px; padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                @error('branch_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Supplier <span class="required">*</span></label>
                                <div class="d-flex align-items-center gap-1">
                                    <select class="form-select" name="supplier_id" id="supplier_id" required style="flex: 1;">
                                        <option value="" selected disabled>Select supplier...</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickSupplierModal()" title="Add New Supplier" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px; padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                @error('supplier_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Purchase Status</label>
                                <select class="form-select fw-medium" name="status">
                                    <option value="received" {{ old('status') == 'received' ? 'selected' : '' }}>Received
                                    </option>
                                    <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>Partial
                                    </option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="ordered" {{ old('status') == 'ordered' ? 'selected' : '' }}>Ordered
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Attach Document</label>
                                <input type="file" class="form-control" name="document">
                                @error('document')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label-sm">Currency</label>
                                <div class="pur-field-group">
                                    <select class="form-select" name="currency" id="currency_selector">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->code }}" data-exchange-rate="{{ $currency->exchange_rate }}" {{ (isset($general_settings['currency']) && $general_settings['currency'] == $currency->code) ? 'selected' : '' }}>
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
                                    id="exchange_rate" value="1.00" readonly>
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
                            @error('product_id')
                                <span class="text-danger">Please add at least one product.</span>
                            @enderror
                        </div>

                        <div class="table-wrapper mb-4">
                            <table class="table erp-table" id="purchase_table">
                                <thead>
                                    <tr>
                                        <th>Product Name / Code</th>
                                        <th class="text-center" style="width: 14%;">Quantity</th>
                                        <th>Batch No</th>
                                        <th>Expired Date</th>
                                        <th class="text-num">Net Unit Cost</th>
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
                            <div class="col-lg-4">
                                <label class="form-label-sm">Note / Remarks</label>
                                <textarea class="form-control" name="note" rows="3" placeholder="Add any additional notes here...">{{ old('note') }}</textarea>
                            </div>
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <label class="form-label-sm">Payment Method</label>
                                        <div class="input-group flex-nowrap pur-field-group">
                                            <select name="payment_method" id="pur_payment_method" class="form-control" title="Payment account">
                                                @forelse ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
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
                                            id="paid_amount" placeholder="0.00" value="{{ old('paid_amount') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label-sm">Order Tax (%)</label>
                                        <input type="number" step="0.01" class="form-control text-end" name="order_tax_rate"
                                            id="order_tax_rate" placeholder="0.00" value="{{ old('order_tax_rate', 0.0) }}">
                                        <input type="hidden" name="order_tax" id="order_tax_value" value="0">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label-sm">Discount</label>
                                        <input type="number" class="form-control text-end" name="order_discount"
                                            id="order_discount" placeholder="0.00"
                                            value="{{ old('order_discount', 0.0) }}">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="form-label-sm">Shipping Cost</label>
                                        <input type="number" class="form-control text-end" name="shipping_cost"
                                            id="shipping_cost" placeholder="0.00"
                                            value="{{ old('shipping_cost', 0.0) }}">
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
                            <i class="fas fa-check-circle me-2"></i>Submit Purchase
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
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
                                <label class="form-label text-muted small fw-bold">Unit Cost</label>
                                <input type="number" class="form-control" id="modal_cost" value="0.00">
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
                            <div id="imeiInputsArea" style="max-height: 250px; overflow-y: auto;">
                            </div>
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
    
    {{-- Quick Add Branch Modal --}}
    <div class="modal fade" id="quickBranchModal" tabindex="-1" aria-labelledby="quickBranchModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h5 class="modal-title fw-bold" id="quickBranchModalTitle"><i class="fas fa-code-branch me-2"></i>Add Branch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickBranchForm">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_branch_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_branch_name" name="name" placeholder="Name" required>
                                <div class="invalid-feedback" id="quick_branch_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_manager_id" class="form-label-sm">Manager <span class="text-danger">*</span></label>
                                <div class="input-group nest-field-group">
                                    <select class="form-select" id="quick_manager_id" name="manager_id" required>
                                        <option value="" disabled selected>Select Manager</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
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
                            <div class="col-12 mb-2">
                                <label for="quick_account_id" class="form-label-sm">Account <span class="text-danger">*</span></label>
                                <div class="input-group nest-field-group">
                                    <select class="form-select" id="quick_account_id" name="account_id" required>
                                        <option value="" disabled selected>Select Account</option>
                                        @foreach ($accounts as $account)
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
                            <div class="col-12 mb-2">
                                <label for="quick_branch_phone" class="form-label-sm">Phone</label>
                                <input type="text" class="form-control" id="quick_branch_phone" name="phone" placeholder="Phone">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_branch_email" class="form-label-sm">Email</label>
                                <input type="email" class="form-control" id="quick_branch_email" name="email" placeholder="Email">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_branch_address" class="form-label-sm">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_branch_address" name="address" placeholder="Address" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_branch_logo" class="form-label-sm">Branch Logo</label>
                                <input type="file" class="form-control" id="quick_branch_logo" name="branch_logo" accept="image/*">
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <label class="form-check-label mb-0 fw-bold" for="quick_branch_status">Is Active</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input switch-size-lg m-0" type="checkbox" checked role="switch" id="quick_branch_status" name="is_active" style="width: 45px; height: 22px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-md" id="quickBranchSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickBranchSpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick Add Supplier Modal --}}
    <div class="modal fade" id="quickSupplierModal" tabindex="-1" aria-labelledby="quickSupplierModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h5 class="modal-title fw-bold" id="quickSupplierModalTitle"><i class="fas fa-truck me-2"></i>Add Supplier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickSupplierForm">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_supplier_name" name="name" placeholder="Name" required>
                                <div class="invalid-feedback" id="quick_supplier_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_company_name" class="form-label-sm">Company Name</label>
                                <input type="text" class="form-control" id="quick_supplier_company_name" name="company_name" placeholder="Company Name">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_phone" class="form-label-sm">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_supplier_phone" name="phone" placeholder="Phone" required>
                                <div class="invalid-feedback" id="quick_supplier_phone_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_email" class="form-label-sm">Email</label>
                                <input type="email" class="form-control" id="quick_supplier_email" name="email" placeholder="Email">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_address" class="form-label-sm">Address</label>
                                <input type="text" class="form-control" id="quick_supplier_address" name="address" placeholder="Address">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_note" class="form-label-sm">Note</label>
                                <input type="text" class="form-control" id="quick_supplier_note" name="note" placeholder="Note">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_supplier_previous_due" class="form-label-sm">Previous Due</label>
                                <input type="number" step="any" class="form-control" id="quick_supplier_previous_due" name="previous_due" placeholder="Previous Due">
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <label class="form-check-label mb-0 fw-bold" for="quick_supplier_status">Is Active</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input switch-size-lg m-0" type="checkbox" checked role="switch" id="quick_supplier_status" name="is_active" style="width: 45px; height: 22px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-md" id="quickSupplierSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickSupplierSpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('backend.purchase.partials.quick_add_account', ['paymentMethodSelectId' => 'pur_payment_method'])
    @include('backend.purchase.partials.quick_add_currency', ['currencySelectId' => 'currency_selector'])

    @include('backend.shared.partials.nested_branch_employee_quick_add', [
        'nx' => [
            'managerSelectId' => 'quick_manager_id',
            'accountSelectId' => 'quick_account_id',
            'departmentSelectId' => 'purCreateUnusedDepartmentSelect',
            'roleSelectId' => 'purCreateUnusedRoleSelect',
            'branchParentModalId' => 'quickBranchModal',
            'employeeParentModalId' => 'quickBranchModal',
            'roles' => $employeeRoles ?? $roles ?? [],
        ],
    ])
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
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#product_search').focus();

            // Product Search
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
                            let html = '';
                            if (data.length > 0) {
                                data.forEach(product => {
                                    let productData = JSON.stringify(product).replace(
                                        /'/g, "&#39;");
                                    html += `
                                <div class="search-item" onclick='addProductToTable(${productData})'>
                                    <div class="fw-bold">${product.product_title}</div>
                                    <small class="text-muted">Code: ${product.product_code} | Cost: ${product.product_cost}</small>
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

            // Add Product Logic
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
                    let qty = product.existing_qty ? parseInt(product.existing_qty) : 1;
                    let cost = parseFloat(product.product_cost);
                    let discount = product.existing_discount ? parseFloat(product.existing_discount) : 0;
                    let taxRate = parseFloat(product.tax) || 0;
                    let taxMethod = product.tax_method || 'no_tax';
                    let imeis = product.existing_imeis ? product.existing_imeis : "";

                    let initialTax = 0;
                    if (product.existing_qty) {
                        let subWithoutTax = (cost * qty) - discount;
                        if (taxMethod !== 'no_tax') {
                            initialTax = subWithoutTax * (taxRate / 100);
                        }
                    } else {
                        // Calculate initial tax for qty 1
                        let subWithoutTax = cost - discount;
                        if (taxMethod !== 'no_tax') {
                            initialTax = subWithoutTax * (taxRate / 100);
                        }
                    }

                    let taxReadonly = taxMethod === 'no_tax' ? 'readonly' : '';
                    let taxClass = taxMethod === 'no_tax' ? 'is-readonly' : '';

                    let imeiHtml = '',
                        imeiHiddenInput = '';
                    let unitName = product.unit ? product.unit.name : 'Piece';

                    if (product.has_imei == 1) {
                        let btnClass = imeis ? 'btn-success' : 'btn-info';
                        let btnText = imeis ? '<i class="fas fa-check"></i> IMEI Saved' :
                            '<i class="fas fa-barcode"></i> Add IMEI';

                        imeiHtml = `
                        <button type="button" class="btn btn-sm ${btnClass} text-white mt-1 open-imei-modal" 
                                data-row="${uniqueRowId}" style="font-size: 10px; padding: 2px 6px;">
                            ${btnText}
                        </button>`;
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
                            <input type="hidden" class="tax-method" value="${taxMethod}">
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
                        <td><input type="text" name="batch_no[]" class="cell-input" placeholder="Batch"></td>
                        <td><input type="date" name="expire_date[]" class="cell-input text-muted"></td>
                        <td><input type="number" step="any" name="net_unit_cost[]" class="cell-input text-num cost-input" value="${cost.toFixed(2)}"></td>
                        <td><input type="number" step="any" name="discount[]" class="cell-input text-num text-danger discount-input" value="${discount.toFixed(2)}"></td>
                        <td>
                            <input type="number" step="any" name="tax[]" class="cell-input text-num tax-input ${taxClass}" value="${initialTax.toFixed(2)}" ${taxReadonly}>
                            <input type="hidden" class="tax-rate" name="tax_rate[]" value="${taxRate}">
                            <input type="hidden" class="manual-tax" value="0">
                        </td>
                        <td class="px-3 text-num fw-bold subtotal">0.00</td>
                        <td class="text-center"><button type="button" class="btn-icon remove-row"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>`;

                    $('#table_body').append(newRow);
                    calculateRow($('#' + uniqueRowId));

                    if (product.has_imei == 1 && !imeis) {
                        setTimeout(() => openImeiModalFunc(uniqueRowId), 200);
                    }
                }
                $('#search_results').hide();
                $('#product_search').val('').focus();
                calculateAll();
            };

            // =========================================
            // IMEI Modal Logic 
            // =========================================
            window.openImeiModalFunc = function(rowId) {
                let row = $('#' + rowId);

                let name = row.find('.product-name').text();
                let code = row.find('.product-code').text();
                let qty = parseInt(row.find('.qty-input').val()) || 1;
                let cost = parseFloat(row.find('.cost-input').val()) || 0;
                let discount = parseFloat(row.find('.discount-input').val()) || 0;
                let tax = parseFloat(row.find('.tax-input').val()) || 0;
                let unit = row.find('.row-unit').val() || 'Piece';
                let savedImeis = row.find('.imei-data-input').val() ? row.find('.imei-data-input').val().split(
                    ',') : [];

                let taxMethod = row.find('.tax-method').val() || 'no_tax';

                $('#currentRowId').val(rowId);
                $('#modalProductTitle').text(`${name} (${code})`);
                $('#modal_qty').val(qty);
                $('#modal_cost').val(cost.toFixed(2));
                $('#modal_discount').val(discount.toFixed(2));
                
                $('#modal_tax').val(tax.toFixed(2));
                if (taxMethod === 'no_tax') {
                    $('#modal_tax').prop('readonly', true).addClass('is-readonly').css({ color: '#4b5563', backgroundColor: '#f3f4f6' });
                } else {
                    $('#modal_tax').prop('readonly', false).removeClass('is-readonly').css({ color: '', backgroundColor: '' });
                }
                
                $('#modal_unit').val(unit);

                generateImeiInputs(qty, savedImeis);
                $('#imeiModal').modal('show');
            };

            function generateImeiInputs(qty, savedValues = []) {
                let container = $('#imeiInputsArea');
                container.empty();

                for (let i = 0; i < qty; i++) {
                    let val = savedValues[i] || '';
                    container.append(`
                    <div class="mb-2">
                        <input type="text" class="form-control modal-imei-field" placeholder="IMEI or Serial #${i + 1}" value="${val}">
                    </div>`);
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
                let newCost = $('#modal_cost').val();
                let newDiscount = $('#modal_discount').val();
                let newTax = $('#modal_tax').val();

                let imeis = [];
                $('.modal-imei-field').each(function() {
                    let val = $(this).val().trim();
                    if (val) imeis.push(val);
                });

                row.find('.qty-input').val(newQty);
                row.find('.cost-input').val(newCost);
                row.find('.discount-input').val(newDiscount);
                row.find('.tax-input').val(newTax);
                row.find('.manual-tax').val('1'); // Mark tax as manually edited
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

            // --- Table Calculations ---
            $(document).on('click', '.qty-btn', function() {
                let input = $(this).closest('.qty-container').find('.qty-input');
                let val = parseInt(input.val()) || 0;
                if ($(this).hasClass('plus')) val++;
                else if (val > 1) val--;
                input.val(val).trigger('change');
            });

            $(document).on('change keyup', '.qty-input', function() {
                // Auto resize based on content
                let valLen = $(this).val().length;
                let newWidth = Math.max(50, valLen * 12 + 20);
                $(this).css('width', newWidth + 'px');
                
                let row = $(this).closest('tr');
                updateImeiButtonState(row, parseInt($(this).val()) || 0);
                calculateRow(row);
                calculateAll();
            });

            $(document).on('change keyup', '.cost-input, .discount-input', function() {
                let row = $(this).closest('tr');
                row.find('.manual-tax').val('0'); // Reset manual tax flag when cost/discount changes so it recalculates
                calculateRow(row);
                calculateAll();
            });

            $(document).on('change keyup', '.tax-input', function() {
                let row = $(this).closest('tr');
                row.find('.manual-tax').val('1'); // Mark as manual so calculateRow doesn't overwrite it
                calculateRow(row);
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
                let cost = parseFloat(row.find('.cost-input').val()) || 0;
                let discount = parseFloat(row.find('.discount-input').val()) || 0;
                let taxRate = parseFloat(row.find('.tax-rate').val()) || 0;
                let taxMethod = row.find('.tax-method').val() || 'no_tax';
                let isManualTax = row.find('.manual-tax').val() === '1';

                let subtotalWithoutTax = (cost * qty) - discount;
                let taxAmount = 0;

                if (taxMethod !== 'no_tax') {
                    if (isManualTax) {
                        taxAmount = parseFloat(row.find('.tax-input').val()) || 0;
                    } else {
                        taxAmount = subtotalWithoutTax * (taxRate / 100);
                        row.find('.tax-input').val(taxAmount.toFixed(2));
                    }
                } else {
                    row.find('.tax-input').val((0).toFixed(2));
                }

                let finalSubtotal = subtotalWithoutTax + taxAmount;
                row.find('.subtotal').text(finalSubtotal.toFixed(2));
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
            // QUOTATION AUTO FILL LOGIC
            // ==========================================
            @if (isset($quotation))
                @if ($quotation->branch_id)
                    $('select[name="branch_id"]').val('{{ $quotation->branch_id }}');
                @endif
                @if ($quotation->supplier_id)
                    $('select[name="supplier_id"]').val('{{ $quotation->supplier_id }}');
                @endif

                $('#order_tax_rate').val('{{ (int) $quotation->order_tax_rate }}');
                $('#order_discount').val('{{ $quotation->order_discount }}');
                $('#shipping_cost').val('{{ $quotation->shipping_cost }}');

                if (`{{ $quotation->note }}`) {
                    $('textarea[name="note"]').val(`{{ $quotation->note }}`);
                }

                @foreach ($quotation->items as $item)
                    addProductToTable({
                        id: "{{ $item->product_id }}",
                        product_title: {{ \Illuminate\Support\Js::from($item->product->product_title) }},
                        product_code: "{{ $item->product->product_code }}",
                        product_cost: "{{ $item->net_unit_price }}",
                        variant_id: "{{ $item->variant_id }}",
                        tax_rate: "{{ $item->tax_rate }}",
                        has_imei: "{{ $item->product->has_imei }}",
                        unit: {
                            name: "{{ $item->product->unit->name ?? 'Piece' }}"
                        },
                        existing_qty: "{{ (int) $item->qty }}",
                        existing_discount: "{{ $item->discount }}",
                        existing_imeis: "{{ $item->imei_number }}"
                    });
                @endforeach

                setTimeout(function() {
                    calculateAll();
                    showFloatingAlert('success', @json(__('Quotation data loaded successfully into Purchase Form!')));
                }, 500);
            @endif

            // Function to show toast alert (matches site-wide floating-alert)
            function showFloatingAlert(type, message) {
                $('.floating-alert').remove(); // Clear existing alert before showing a new one
                var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
                var alertClass = type === 'success' ? 'success' : 'danger';
                var $alert = $('<div class="alert alert-' + alertClass + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
                $('body').append($alert);
                setTimeout(function() {
                    $alert.fadeOut(300, function() { $(this).remove(); });
                }, 5000);
            }

            // ==========================================
            // Quick Add Branch & Supplier JS
            // Hard rule: quick-add must NEVER show toaster / floating success alert.
            // ==========================================
            let quickBranchModalInstance;
            let quickSupplierModalInstance;

            function mutePurCreateQuickAddPopups(on) {
                window.__purCreateQuickAddSilent = !!on;
                if (typeof toastr !== 'undefined') {
                    if (on) {
                        if (!window.__purCreateToastrBackup) {
                            window.__purCreateToastrBackup = {
                                success: toastr.success,
                                error: toastr.error,
                                info: toastr.info,
                                warning: toastr.warning
                            };
                        }
                        toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
                    } else if (window.__purCreateToastrBackup) {
                        toastr.success = window.__purCreateToastrBackup.success;
                        toastr.error = window.__purCreateToastrBackup.error;
                        toastr.info = window.__purCreateToastrBackup.info;
                        toastr.warning = window.__purCreateToastrBackup.warning;
                    }
                }
            }

            if (!window.__purCreateAlertGuardInstalled) {
                window.__purCreateAlertGuardInstalled = true;
                window.__purCreateQuickAddSilent = false;
                const nativeAlert = window.alert;
                window.alert = function () {
                    if (window.__purCreateQuickAddSilent) return;
                    return nativeAlert.apply(window, arguments);
                };
                if (typeof window.showAppToast === 'function') {
                    const nativeToast = window.showAppToast;
                    window.showAppToast = function () {
                        if (window.__purCreateQuickAddSilent) return;
                        return nativeToast.apply(window, arguments);
                    };
                }
            }
            
            const branchModalEl = document.getElementById('quickBranchModal');
            if (branchModalEl) {
                quickBranchModalInstance = new bootstrap.Modal(branchModalEl);
            }
            const supplierModalEl = document.getElementById('quickSupplierModal');
            if (supplierModalEl) {
                quickSupplierModalInstance = new bootstrap.Modal(supplierModalEl);
            }

            window.openQuickBranchModal = function() {
                $('#quickBranchForm')[0].reset();
                $('#quick_branch_name').removeClass('is-invalid');
                $('#quick_branch_error').text('');
                $('#quick_branch_status').prop('checked', true);
                quickBranchModalInstance.show();
                setTimeout(() => $('#quick_branch_name').focus(), 300);
            };

            window.openQuickSupplierModal = function() {
                $('#quickSupplierForm')[0].reset();
                $('#quick_supplier_name, #quick_supplier_phone').removeClass('is-invalid');
                $('#quick_supplier_error, #quick_supplier_phone_error').text('');
                $('#quick_supplier_status').prop('checked', true);
                quickSupplierModalInstance.show();
                setTimeout(() => $('#quick_supplier_name').focus(), 300);
            };

            $('#quickBranchForm').on('submit', function(e) {
                e.preventDefault();

                const name = $('#quick_branch_name').val().trim();
                const address = $('#quick_branch_address').val().trim();
                const account_id = $('#quick_account_id').val();
                const manager_id = $('#quick_manager_id').val();
                let isValid = true;

                if (!name) {
                    $('#quick_branch_name').addClass('is-invalid');
                    $('#quick_branch_error').text(@json(__('Name is required.')));
                    isValid = false;
                }

                if(!isValid || !address || !account_id || !manager_id) return;

                $('#quickBranchSaveBtn').prop('disabled', true);
                $('#quickBranchSpinner').removeClass('d-none');
                $('#quick_branch_name').removeClass('is-invalid');

                var formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                if (!$('#quick_branch_status').is(':checked')) {
                    formData.delete('is_active');
                }

                mutePurCreateQuickAddPopups(true);
                $.ajax({
                    url: "{{ route('branches.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            const newOption = new Option(response.branch.name, response.branch.id, true, true);
                            $('#branch_id').append(newOption);

                            quickBranchModalInstance.hide();
                            // No toaster / floating alert on quick-add
                        }
                    },
                    error: function(xhr) {
                        $('#quick_branch_name').addClass('is-invalid');
                        const msg = xhr.responseJSON?.message || @json(__('Failed to create. Check required fields.'));
                        $('#quick_branch_error').text(msg);
                    },
                    complete: function() {
                        mutePurCreateQuickAddPopups(false);
                        $('#quickBranchSaveBtn').prop('disabled', false);
                        $('#quickBranchSpinner').addClass('d-none');
                    },
                });
            });

            $('#quickSupplierForm').on('submit', function(e) {
                e.preventDefault();

                const name = $('#quick_supplier_name').val().trim();
                const phone = $('#quick_supplier_phone').val().trim();
                let isValid = true;

                if (!name) {
                    $('#quick_supplier_name').addClass('is-invalid');
                    $('#quick_supplier_error').text(@json(__('Name is required.')));
                    isValid = false;
                }
                if (!phone) {
                    $('#quick_supplier_phone').addClass('is-invalid');
                    $('#quick_supplier_phone_error').text(@json(__('Phone is required.')));
                    isValid = false;
                }

                if(!isValid) return;

                $('#quickSupplierSaveBtn').prop('disabled', true);
                $('#quickSupplierSpinner').removeClass('d-none');
                $('#quick_supplier_name, #quick_supplier_phone').removeClass('is-invalid');

                var formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                if (!$('#quick_supplier_status').is(':checked')) {
                    formData.delete('is_active');
                }

                mutePurCreateQuickAddPopups(true);
                $.ajax({
                    url: "{{ route('suppliers.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            const newOption = new Option(response.supplier.name, response.supplier.id, true, true);
                            $('#supplier_id').append(newOption);

                            quickSupplierModalInstance.hide();
                            // No toaster / floating alert on quick-add
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.phone) {
                                $('#quick_supplier_phone').addClass('is-invalid');
                                $('#quick_supplier_phone_error').text(errors.phone[0]);
                            }
                        } else {
                            $('#quick_supplier_name').addClass('is-invalid');
                            $('#quick_supplier_error').text(@json(__('Failed to create. Please try again.')));
                        }
                    },
                    complete: function() {
                        mutePurCreateQuickAddPopups(false);
                        $('#quickSupplierSaveBtn').prop('disabled', false);
                        $('#quickSupplierSpinner').addClass('d-none');
                    },
                });
            });

            // Purchase Form AJAX Submission
            $('#purchase-form').on('submit', function(e) {
                e.preventDefault();
                
                let form = $(this);
                let submitBtn = form.find('.btn-submit');
                let originalBtnText = submitBtn.html();
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback.ajax-error').remove();
                
                // Ensure at least one product is added
                if ($('.product-row').length === 0) {
                    showFloatingAlert('error', @json(__('Please add at least one product before submitting.')));
                    return false;
                }
                
                // Show loading state
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                showFloatingAlert('success', response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let firstError = '';
                            
                            $.each(errors, function(field, messages) {
                                if (!firstError) firstError = messages[0];
                                
                                let fieldParts = field.split('.');
                                let inputName = fieldParts[0];
                                let isArray = fieldParts.length > 1;
                                
                                if (isArray) {
                                   inputName = fieldParts[0] + '[]';
                                }
                                
                                let inputElement = form.find('[name="' + inputName + '"]');
                                
                                if (isArray) {
                                    let index = parseInt(fieldParts[1]);
                                    inputElement = form.find('[name="' + inputName + '"]').eq(index);
                                }
                                
                                if (inputElement.length) {
                                    inputElement.addClass('is-invalid');
                                    // Remove inline text appending to prevent flex layout shrinking
                                }
                            });
                            
                            showFloatingAlert('error', firstError || @json(__('Please check the form for errors.')));
                        } else {
                            let errorMsg = xhr.responseJSON?.message || @json(__('An error occurred while saving the purchase.'));
                            showFloatingAlert('error', errorMsg);
                        }
                    }
                });
            });
        });
    </script>
@endpush
