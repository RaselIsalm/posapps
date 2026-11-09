<form id="editReturnForm" action="{{ route('purchase-return.update', $return->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <div class="col-md-6">
            <p class="mb-1"><strong>Purchase Invoice:</strong> <span
                    class="text-primary">{{ $return->purchase->invoice_no }}</span></p>
            <p class="mb-1"><strong>Supplier:</strong> {{ $return->supplier->name }}</p>
        </div>
        <div class="col-md-6 text-end">
            <p class="mb-1"><strong>Return Date:</strong> {{ date('d M, Y', strtotime($return->return_date)) }}</p>
            <p class="mb-1"><strong>Ref No:</strong> {{ $return->reference_no }}</p>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle table-sm">
            <thead class="bg-light">
                <tr>
                    <th>Product Name</th>
                    <th class="text-end" width="120">Unit Price</th>
                    <th class="text-center" width="100">Stock Qty</th>
                    <th class="text-center" width="120">Return Qty</th>
                    <th class="text-end" width="120">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach ($return->purchase->details as $purchaseDetail)
                    @php
                        $existingReturn = $return->details
                            ->where('product_id', $purchaseDetail->product_id)
                            ->where('variant_id', $purchaseDetail->variant_id)
                            ->first();

                        $returnQty = $existingReturn ? $existingReturn->qty : 0;
                        $lineTotal = $returnQty * $purchaseDetail->net_unit_cost;
                        $grandTotal += $lineTotal;
                    @endphp

                    <tr>
                        <td>
                            <span class="fw-bold text-dark">{{ $purchaseDetail->product->name }}</span>
                            @if ($purchaseDetail->variant)
                                <br><small class="text-muted">{{ $purchaseDetail->variant->name }}</small>
                            @endif
                            <input type="hidden" name="return_items[]" value="{{ $purchaseDetail->product_id }}">
                            <input type="hidden" name="variant_id[]" value="{{ $purchaseDetail->variant_id }}">
                        </td>
                        <td class="text-end">
                            {{ \App\Helpers\CurrencyHelper::format($purchaseDetail->net_unit_cost, 2) }}
                            <input type="hidden" name="unit_price[]" class="unit-price"
                                value="{{ $purchaseDetail->net_unit_cost }}">
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $purchaseDetail->qty }}</span>
                        </td>
                        <td>
                            <input type="number" name="qty[]"
                                class="form-control form-control-sm text-center return-qty" value="{{ $returnQty }}"
                                min="0" max="{{ $purchaseDetail->qty }}" step="any">
                        </td>
                        <td class="text-end fw-bold subtotal">
                            {{ \App\Helpers\CurrencyHelper::format($lineTotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold text-dark">Grand Total:</td>
                    <td class="text-end fw-bold text-dark" id="editGrandTotal">{{ \App\Helpers\CurrencyHelper::format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Return Note</label>
            <textarea name="return_note" class="form-control" rows="2" placeholder="Reason for return...">{{ $return->return_note }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Staff Note</label>
            <textarea name="staff_note" class="form-control" rows="2" placeholder="Internal note...">{{ $return->staff_note }}</textarea>
        </div>
    </div>

    <div class="text-end pt-2 border-top">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Return</button>
    </div>
</form>

<script>
    // Live Calculation for Edit Form
    $('.return-qty').on('input', function() {
        let row = $(this).closest('tr');
        let qty = parseFloat($(this).val()) || 0;
        let price = parseFloat(row.find('.unit-price').val()) || 0;
        let maxQty = parseFloat($(this).attr('max'));

        if (qty > maxQty) {
            if(typeof showFloatingAlert === 'function') {
                showFloatingAlert('danger', @json(__('Cannot return more than purchased quantity (:qty)')).replace(':qty', maxQty));
            }
            $(this).val(maxQty);
            qty = maxQty;
        }

        let subtotal = qty * price;
        row.find('.subtotal').text(subtotal.toFixed(2));

        let total = 0;
        $('.subtotal').each(function() {
            total += parseFloat($(this).text()) || 0;
        });
        $('#editGrandTotal').text(total.toFixed(2));
    });
</script>
