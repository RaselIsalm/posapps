@extends('backend.layout.main')

@section('title', 'Edit Sale')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        .qty-input {
            width: 80px;
            text-align: center;
            font-weight: bold;
        }

        .price-input {
            width: 100px;
            text-align: right;
        }

        .total-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .table-custom thead th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .remove-btn {
            color: #ef4444;
            cursor: pointer;
            transition: 0.2s;
        }

        .remove-btn:hover {
            color: #dc2626;
            transform: scale(1.1);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold text-dark">Edit Sale <span
                        class="text-primary">#{{ $sale->invoice_no ?? $sale->id }}</span></h4>
                <p class="text-muted mb-0">Update products, quantities and pricing.</p>
            </div>
            <a href="{{ route('sale-list.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('sale-list.update', $sale->id) }}" method="POST" id="edit-sale-form">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <label class="form-label fw-bold">Add Product</label>
                                <select id="product_selector" class="form-select select2">
                                    <option value="">Type to search product...</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}"
                                            data-name="{{ $product->product_title }} - {{ $product->product_code }}">
                                            {{ $product->product_title }} (Code: {{ $product->product_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-center" width="50"><i class="fas fa-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart_table_body">
                                    </tbody>
                                </table>
                            </div>

                            <div id="empty_cart_msg" class="text-center py-5 text-muted d-none">
                                <i class="fas fa-shopping-cart fa-3x mb-3 opacity-25"></i>
                                <p>No products added yet.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Order Summary</h6>
                            <div class="total-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="fw-bold" id="display_sub_total">0.00</span>
                                    <input type="hidden" name="sub_total" id="sub_total">
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Discount</span>
                                    <div class="input-group input-group-sm w-50">
                                        <input type="number" name="discount_amount" id="discount_input"
                                            class="form-control text-end" value="{{ $sale->discount_amount ?? 0 }}"
                                            min="0">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Order Tax (%)</span>
                                    <div class="input-group input-group-sm w-50">
                                        <input type="number" name="tax_percent" id="tax_input"
                                            class="form-control text-end" value="{{ $sale->tax_percent ?? 0 }}"
                                            min="0">
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h6 fw-bold mb-0 text-dark">Grand Total</span>
                                    <span class="h4 fw-bold text-primary mb-0" id="display_grand_total">0.00</span>
                                    <input type="hidden" name="grand_total" id="grand_total">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Settings</h6>

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <div class="input-group sale-field-group">
                                    <select class="form-select select2" name="customer_id" id="customer_id">
                                        <option value="">Walk in / Select customer...</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ (string) $sale->customer_id === (string) $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_customers')
                                        <button class="btn btn-outline-primary btn-sale-add" type="button"
                                            onclick="openSaleCustomerModal()" title="Add New Customer">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <div class="input-group sale-field-group">
                                    <select class="form-select select2" name="branch_id" id="branch_id" required>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ (string) $sale->branch_id === (string) $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_branches')
                                        <button class="btn btn-outline-primary btn-sale-add" type="button"
                                            onclick="openSaleBranchModal()" title="Add New Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Salesman</label>
                                <div class="input-group sale-field-group">
                                    <select class="form-select select2" name="seller_id" id="seller_id">
                                        <option value="">Select salesman...</option>
                                        @foreach ($sellers ?? [] as $seller)
                                            <option value="{{ $seller->id }}"
                                                {{ (string) $sale->seller_id === (string) $seller->id ? 'selected' : '' }}>
                                                {{ $seller->name }}{{ $seller->phone ? ' ('.$seller->phone.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_employees')
                                        <button class="btn btn-outline-primary btn-sale-add" type="button"
                                            onclick="openSaleSalesmanModal()" title="Add New Salesman">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="input-group sale-field-group">
                                    <select class="form-select select2" name="payment_account_id" id="sale_payment_account_id">
                                        <option value="">Select payment account...</option>
                                        @foreach ($accounts ?? [] as $account)
                                            <option value="{{ $account->id }}"
                                                {{ (string) ($currentPaymentAccountId ?? '') === (string) $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}{{ $account->type ? ' ('.$account->type.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button class="btn btn-outline-primary btn-sale-add" type="button"
                                            onclick="openPurAccountModal()" title="Add Payment Method">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                                <small class="text-muted">Updates the first payment label if a payment exists. Use Due Payment to add new payments.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="sale_date" class="form-control"
                                    value="{{ $sale->created_at->format('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <textarea name="note" class="form-control" rows="2">{{ $sale->note }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-uppercase">
                                <i class="fas fa-save me-1"></i> Update Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('backend.sale_list.partials.quick_add')
    @include('backend.purchase.partials.quick_add_account', [
        'paymentMethodSelectId' => 'sale_payment_account_id',
    ])
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        $(document).ready(function() {
            // Initialize Plugins
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            let cart = [];
            const existingItems = @json($sale->productSales);
            existingItems.forEach(item => {
                cart.push({
                    id: item.product_id,
                    name: item.product ? item.product.product_title : @json(__('Unknown Product')),
                    price: parseFloat(item.unit_price || item.net_unit_price || item.price || 0),
                    qty: parseInt(item.qty || item.quantity || 1)
                });
            });

            renderCart();

            // Add Product Logic
            $('#product_selector').on('select2:select', function(e) {
                let data = e.params.data;
                let productId = data.id;
                let productName = $(data.element).data('name');
                let productPrice = parseFloat($(data.element).data('price'));
                let existingItem = cart.find(item => item.id == productId);

                if (existingItem) {
                    existingItem.qty += 1;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        qty: 1
                    });
                }
                $('#product_selector').val(null).trigger('change');
                renderCart();
            });

            // Render Table Function
            function renderCart() {
                let tbody = $('#cart_table_body');
                tbody.empty();

                if (cart.length === 0) {
                    $('#empty_cart_msg').removeClass('d-none');
                } else {
                    $('#empty_cart_msg').addClass('d-none');

                    cart.forEach((item, index) => {
                        let subtotal = item.price * item.qty;
                        let row = `
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">${item.name}</span>
                            </td>
                            <td class="text-center">
                                <input type="number" step="any" class="form-control price-input mx-auto" value="${item.price}" onchange="updateItem(${index}, 'price', this.value)">
                            </td>
                            <td class="text-center">
                                <div class="input-group justify-content-center">
                                    <button type="button" class="btn btn-sm btn-light border" onclick="updateQty(${index}, -1)">-</button>
                                    <input type="number" class="form-control qty-input p-0" value="${item.qty}" readonly>
                                    <button type="button" class="btn btn-sm btn-light border" onclick="updateQty(${index}, 1)">+</button>
                                </div>
                            </td>
                            <td class="text-end fw-bold">
                                ${subtotal.toFixed(2)}
                            </td>
                            <td class="text-center">
                                <i class="fas fa-trash-alt remove-btn" onclick="removeItem(${index})"></i>
                            </td>
                        </tr>
                    `;
                        tbody.append(row);
                    });
                }
                calculateTotals();
            }

            // Global Functions
            window.updateQty = function(index, change) {
                if (cart[index].qty + change > 0) {
                    cart[index].qty += change;
                    renderCart();
                }
            };

            window.updateItem = function(index, field, value) {
                if (value >= 0) {
                    cart[index][field] = parseFloat(value);
                    renderCart();
                }
            };

            window.removeItem = function(index) {
                window.showAppConfirm({
                    title: @json(__('Remove this item?')),
                    text: @json(__('This product will be removed from the sale.')),
                    confirmText: @json(__('Yes, remove'))
                }).then(function (ok) {
                    if (ok) {
                        cart.splice(index, 1);
                        renderCart();
                    }
                });
            };

            // Calculate Totals
            function calculateTotals() {
                let subTotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                let discount = parseFloat($('#discount_input').val()) || 0;
                let taxPercent = parseFloat($('#tax_input').val()) || 0;

                // Tax Calculation
                let taxAmount = (subTotal * taxPercent) / 100;
                let grandTotal = (subTotal + taxAmount) - discount;

                // DOM Update
                $('#display_sub_total').text(subTotal.toFixed(2));
                $('#sub_total').val(subTotal);
                $('#display_grand_total').text(grandTotal.toFixed(2));
                $('#grand_total').val(grandTotal);
            }

            $('#discount_input, #tax_input').on('input', function() {
                calculateTotals();
            });
            $('#edit-sale-form').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

                const payload = {
                    _token: $('input[name="_token"]').val(),
                    _method: 'PUT',

                    customer_id: $('select[name="customer_id"]').val(),
                    branch_id: $('select[name="branch_id"]').val(),
                    seller_id: $('select[name="seller_id"]').val() || null,
                    payment_account_id: $('select[name="payment_account_id"]').val() || null,
                    sale_date: $('input[name="sale_date"]').val(),
                    note: $('textarea[name="note"]').val(),
                    
                    sub_total: $('#sub_total').val(),
                    discount_amount: $('#discount_input').val(),
                    tax_percent: $('#tax_input').val(),
                    grand_total: $('#grand_total').val(),
                    items: cart 
                };
                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: payload,
                    success: function(response) {
                        localStorage.setItem('sale_success_msg', response.message || @json(__('Sale updated successfully!')));
                        window.location.href = "{{ route('sale-list.index') }}"; 
                    },
                    error: function(xhr) {
                        let errorMsg = @json(__('Something went wrong!'));
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showCustomToast(errorMsg, 'error');
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

        });
    </script>
@endpush