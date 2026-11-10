@extends('backend.layout.main')
@section('title', 'Edit Transfer')

@push('css')
    <style>
        #transfer_table_body tr td,
        #transfer_table_body input.form-control,
        tfoot tr td,
        tfoot span,
        .text-black-force {
            color: #000 !important;
            font-weight: 500;
        }

        .form-label {
            color: #000;
            font-weight: 600;
        }

        .variant-text {
            color: #555 !important;
            font-size: 0.85em;
            font-weight: normal;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-black-force">Edit Transfer <small class="text-muted">(Ref:
                        {{ $transfer->reference_no }})</small></h5>
                <a href="{{ route('transfers.index') }}" class="btn btn-primary btn-sm px-3 py-2">Back to List</a>
            </div>
            <div class="card-body">

                <form action="{{ route('transfers.update', $transfer->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Branch Selection --}}
                    <div class="row mb-3 g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Date</label>
                            <input type="text" class="form-control text-black-force"
                                value="{{ date('d-m-Y', strtotime($transfer->created_at)) }}" disabled>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">From Branch *</label>
                            <div class="input-group tr-field-group">
                                <select name="from_branch_id" id="from_branch_id" class="form-select text-black-force" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $transfer->from_branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_branches')
                                    <button type="button" class="btn btn-outline-primary btn-tr-add"
                                        onclick="openTrBranchModal('from_branch_id')" title="Add Branch">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">To Branch *</label>
                            <div class="input-group tr-field-group">
                                <select name="to_branch_id" id="to_branch_id" class="form-select text-black-force" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $transfer->to_branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_branches')
                                    <button type="button" class="btn btn-outline-primary btn-tr-add"
                                        onclick="openTrBranchModal('to_branch_id')" title="Add Branch">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select text-black-force">
                                <option value="request" {{ $transfer->status == 'request' ? 'selected' : '' }}>Request
                                    (Requisition)</option>
                                <option value="sent" {{ $transfer->status == 'sent' ? 'selected' : '' }}>Sent (Pending
                                    Receive)</option>
                            </select>
                            @if ($transfer->status == 'sent')
                                <small class="text-danger fw-bold mt-1 d-block"><i class="fas fa-exclamation-triangle"></i>
                                    Changing 'Sent' to 'Request' will RESTORE stock to Sender.</small>
                            @endif
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="mb-4">
                        <label class="form-label">Add Product</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-black-force"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="product_search" class="form-control text-black-force"
                                placeholder="Type product code or name and select...">
                        </div>
                        <div id="product_list" class="list-group position-absolute w-100 shadow"
                            style="z-index: 999; display:none;"></div>
                    </div>

                    {{-- Order Table --}}
                    <h6 class="fw-bold mb-3 text-black-force">Order Table *</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle bg-light" style="white-space: nowrap; min-width: 800px;">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th class="text-black">Name</th>
                                    <th class="text-black">Code</th>
                                    <th width="100" class="text-black">Quantity</th>
                                    <th width="120" class="text-black">Net Unit Cost</th>
                                    <th width="100" class="text-black">Tax</th>
                                    <th width="120" class="text-black">SubTotal</th>
                                    <th width="50" class="text-danger"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="transfer_table_body" class="text-black-force">
                                {{-- Pre-fill Existing Items --}}
                                @foreach ($transfer->details as $detail)
                                    @php
                                        $uniqueId = $detail->variant_id
                                            ? $detail->product_id . '_' . $detail->variant_id
                                            : $detail->product_id;
                                        $variantText = '';

                                        if ($detail->variant_id && $detail->variant) {
                                            if (!empty($detail->variant->variant_name)) {
                                                $variantText = ' (' . $detail->variant->variant_name . ')';
                                            }
                                            elseif (!empty($detail->variant->name)) {
                                                $variantText = ' (' . $detail->variant->name . ')';
                                            }
                                            elseif (!empty($detail->variant->item_code)) {
                                                $variantText = ' (' . $detail->variant->item_code . ')';
                                            }
                                            else {
                                                $variantText = ' (Var: ' . $detail->variant->id . ')';
                                            }
                                        }
                                    @endphp

                                    <tr id="row_{{ $uniqueId }}">
                                        <td>
                                            <span
                                                class="fw-bold">{{ $detail->product->product_title ?? 'Product Not Found' }}</span>
                                            <span class="variant-text text-muted small">{{ $variantText }}</span>

                                            <input type="hidden" name="product_id[]" value="{{ $detail->product_id }}">
                                            <input type="hidden" name="variant_id[]" value="{{ $detail->variant_id }}">
                                        </td>
                                        <td>{{ $detail->variant->item_code ?? $detail->product->product_code }}</td>
                                        <td>
                                            <input type="number" name="qty[]" id="qty_{{ $uniqueId }}"
                                                class="form-control form-control-sm qty-input text-center fw-bold text-black-force"
                                                value="{{ $detail->qty }}" min="1"
                                                oninput="calculateRow('{{ $uniqueId }}')"
                                                data-stock="{{ $detail->product->productBranches->where('branch_id', $transfer->from_branch_id)->first()->qty ?? 0 }}">
                                            <small class="text-muted">Stock: {{ $detail->product->productBranches->where('branch_id', $transfer->from_branch_id)->first()->qty ?? 0 }}</small>
                                        </td>
                                        <td class="text-end">
                                            {{ \App\Helpers\CurrencyHelper::format($detail->net_unit_cost, 2) }}
                                            <input type="hidden" name="net_unit_cost[]" id="price_{{ $uniqueId }}"
                                                value="{{ $detail->net_unit_cost }}">
                                        </td>
                                        <td class="text-end">
                                            <span
                                                id="tax_display_{{ $uniqueId }}">{{ \App\Helpers\CurrencyHelper::format($detail->tax, 2) }}</span>
                                            <input type="hidden" name="tax[]" id="tax_{{ $uniqueId }}"
                                                value="{{ $detail->tax }}">
                                            @php
                                                $taxRate = 0;
                                                if ($detail->net_unit_cost > 0 && $detail->qty > 0) {
                                                    $taxRate = ($detail->tax / ($detail->net_unit_cost * $detail->qty)) * 100;
                                                }
                                            @endphp
                                            <input type="hidden" id="tax_rate_{{ $uniqueId }}" class="tax-rate" value="{{ $taxRate }}">
                                        </td>
                                        <td class="text-end fw-bold">
                                            <span
                                                id="subtotal_display_{{ $uniqueId }}">{{ \App\Helpers\CurrencyHelper::format($detail->subtotal, 2) }}</span>
                                            <input type="hidden" name="subtotal[]" class="subtotal-input"
                                                id="subtotal_{{ $uniqueId }}" value="{{ $detail->subtotal }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                    class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-white fw-bold text-black-force">
                                <tr>
                                    <td colspan="2" class="text-end">Total</td>
                                    <td id="total-qty" class="text-center">{{ $transfer->total_qty }}</td>
                                    <td></td>
                                    <td id="total-tax" class="text-end">0.00</td>
                                    <td id="total-subtotal" class="text-end">
                                        {{ \App\Helpers\CurrencyHelper::format($transfer->grand_total - $transfer->shipping_cost, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Shipping & Document --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Shipping Cost</label>
                            <input type="number" name="shipping_cost" id="shipping_cost"
                                class="form-control text-black-force fw-bold" step="any"
                                value="{{ $transfer->shipping_cost }}" oninput="calculateGrandTotal()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attach Document</label>
                            <input type="file" name="document" class="form-control text-black-force">
                            @if ($transfer->document)
                                <small class="text-success fw-bold">Current File: {{ $transfer->document }}</small>
                            @endif
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="mb-4">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control text-black-force" rows="3">{{ $transfer->note }}</textarea>
                    </div>

                    {{-- Final Total Display --}}
                    <div class="d-flex justify-content-end mb-4">
                        <h4 class="fw-bold text-black-force">Grand Total: <span
                                id="grand_total_display">{{ \App\Helpers\CurrencyHelper::format($transfer->grand_total, 2) }}</span></h4>
                        <input type="hidden" name="grand_total" id="grand_total_input"
                            value="{{ $transfer->grand_total }}">
                    </div>

                    {{-- Submit Button --}}
                    <div class="text-start">
                        <button type="submit" class="btn btn-primary px-4">Update Transfer</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @include('backend.transfers.partials.quick_add')
@endsection

@push('js')
    <script>
        $(document).ready(function() {


            // Search Logic
            $('#product_search').on('keyup', function() {
                let query = $(this).val();
                let branchId = $('#from_branch_id').val();
                if (query.length > 2) {
                    $.ajax({
                        url: "{{ route('transfers.get_product') }}",
                        type: 'GET',
                        data: {
                            search: query,
                            branch_id: branchId
                        },
                        success: function(res) {
                            let html = '';
                            if (res.length > 0) {
                                res.forEach(item => {
                                    let varId = item.variant_id ? item.variant_id : '';
                                    html += `<a href="javascript:void(0)" class="list-group-item list-group-item-action select-product text-black-force" 
                                            data-id="${item.id}" data-variant-id="${varId}" data-name="${item.name}" 
                                            data-code="${item.code}" data-price="${item.price}" data-stock="${item.stock}">
                                            ${item.name} (${item.code}) - Stock: ${item.stock}
                                         </a>`;
                                });
                                $('#product_list').html(html).show();
                            } else {
                                $('#product_list').hide();
                            }
                        }
                    });
                } else {
                    $('#product_list').hide();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#product_search, #product_list').length) {
                    $('#product_list').hide();
                }
            });

            // Add Product Logic
            $(document).on('click', '.select-product', function() {
                let id = $(this).data('id'),
                    variantId = $(this).data('variant-id'),
                    name = $(this).data('name'),
                    code = $(this).data('code'),
                    price = parseFloat($(this).data('price')),
                    stock = $(this).data('stock');
                let uniqueId = variantId ? `${id}_${variantId}` : `${id}`;

                if ($(`#row_${uniqueId}`).length > 0) {
                    let qtyInput = $(`#qty_${uniqueId}`);
                    qtyInput.val(parseFloat(qtyInput.val()) + 1);
                    calculateRow(uniqueId);
                } else {
                    let row = `<tr id="row_${uniqueId}">
                    <td>${name}
                        <input type="hidden" name="product_id[]" value="${id}"><input type="hidden" name="variant_id[]" value="${variantId}"></td>
                    <td>${code}</td>
                    <td>
                        <input type="number" name="qty[]" id="qty_${uniqueId}" class="form-control form-control-sm qty-input text-center fw-bold text-black-force" value="1" min="1" oninput="calculateRow('${uniqueId}')" data-stock="${stock}">
                        <small class="text-muted">Stock: ${stock}</small>
                    </td>
                    <td class="text-end">${price.toFixed(2)}
                        <input type="hidden" name="net_unit_cost[]" id="price_${uniqueId}" value="${price}"></td>
                    <td class="text-end"><span id="tax_display_${uniqueId}">0.00</span><input type="hidden" name="tax[]" id="tax_${uniqueId}" value="0"><input type="hidden" class="tax-rate" value="0"></td>
                    <td class="text-end fw-bold"><span id="subtotal_display_${uniqueId}">${price.toFixed(2)}</span>
                        <input type="hidden" name="subtotal[]" class="subtotal-input" id="subtotal_${uniqueId}" value="${price}"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                    $('#transfer_table_body').append(row);
                    calculateRow(uniqueId);
                }
                $('#product_search').val('');
                $('#product_list').hide();
            });

            // Remove & Calculation Logic
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            });

            window.calculateRow = function(uid) {
                let qtyInput = $(`#qty_${uid}`);
                let qty = parseFloat(qtyInput.val()) || 0;
                let stock = parseFloat(qtyInput.data('stock')) || 0;

                if (qty > stock) {
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Insufficient Stock! You cannot transfer more than available stock (:stock)')).replace(':stock', stock));
                    }
                    qtyInput.val(stock);
                    qty = stock;
                }

                let price = parseFloat($(`#price_${uid}`).val()) || 0;
                let taxRate = parseFloat($(`#tax_rate_${uid}`).val()) || 0;

                let taxAmount = (price * taxRate / 100) * qty;
                let subtotal = (price * qty) + taxAmount;

                $(`#tax_${uid}`).val(taxAmount.toFixed(2));
                $(`#tax_display_${uid}`).text(taxAmount.toFixed(2));

                $(`#subtotal_${uid}`).val(subtotal.toFixed(2));
                $(`#subtotal_display_${uid}`).text(subtotal.toFixed(2));

                calculateGrandTotal();
            }

            window.calculateGrandTotal = function() {
                let totalQty = 0,
                    totalSubtotal = 0;
                $('.qty-input').each(function() {
                    totalQty += parseFloat($(this).val()) || 0;
                });
                $('.subtotal-input').each(function() {
                    totalSubtotal += parseFloat($(this).val()) || 0;
                });
                let shipping = parseFloat($('#shipping_cost').val()) || 0;
                let grandTotal = totalSubtotal + shipping;

                $('#total-qty').text(totalQty);
                $('#total-subtotal').text(totalSubtotal.toFixed(2));
                $('#grand_total_display').text(grandTotal.toFixed(2));
                $('#grand_total_input').val(grandTotal.toFixed(2));
            }
        });

        function showFloatingAlert(type, message) {
            $('.floating-alert').remove(); // Clear existing
            var label = type === 'success' ? @json(__('Success!')) : type === 'warning' ? @json(__('Warning!')) : @json(__('Error!'));
            var bsClass = type === 'error' ? 'danger' : type;
            var $alert = $('<div class="alert alert-' + bsClass + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }
    </script>
@endpush
