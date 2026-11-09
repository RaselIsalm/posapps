<form action="{{ route('purchase-return.store') }}" method="POST">
    @csrf
    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

    {{-- Basic Info --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <p class="mb-1 text-muted small">Supplier</p>
            <h6 class="fw-bold">{{ $purchase->supplier->name }}</h6>
        </div>
        <div class="col-md-4">
            <p class="mb-1 text-muted small">Purchase Reference</p>
            <h6 class="fw-bold text-primary">{{ $purchase->invoice_no }}</h6>
        </div>
        <div class="col-md-4">
            <p class="mb-1 text-muted small">Branch</p>
            <h6 class="fw-bold">{{ $purchase->branch->name }}</h6>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
            <thead class="bg-light">
                <tr>
                    <th width="40%">Product</th>
                    <th class="text-center">Purchased Qty</th>
                    <th class="text-center">Purchase Price</th>
                    <th width="15%">Return Qty</th>
                    <th class="text-end">Return Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->details as $key => $detail)
                    <tr>
                        <td>
                            <span class="fw-bold">{{ $detail->product->name }}</span>
                            @if ($detail->variant)
                                <br><small class="text-muted">{{ $detail->variant->name }}</small>
                            @endif
                            <input type="hidden" name="return_items[]" value="{{ $detail->product_id }}">
                            <input type="hidden" name="variant_id[]" value="{{ $detail->variant_id }}">
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $detail->qty }}</span>
                        </td>
                        <td class="text-center">
                            <input type="hidden" class="unit-price" name="unit_price[]"
                                value="{{ $detail->net_unit_cost }}">
                            {{ \App\Helpers\CurrencyHelper::format($detail->net_unit_cost, 2) }}
                        </td>
                        <td>
                            <input type="number" name="qty[]"
                                class="form-control form-control-sm return-qty text-center" min="0"
                                max="{{ $detail->qty }}" value="0" oninput="calculateRow(this)">
                        </td>
                        <td class="text-end">
                            <span class="row-total fw-bold">0.00</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold">Grand Total Return Amount:</td>
                    <td class="text-end fw-bold text-danger fs-5" id="grandTotalDisplay">0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Notes --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Return Note (Reason)</label>
            <textarea name="return_note" class="form-control" rows="2" placeholder="e.g. Damaged product, Wrong item..."></textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Staff Note</label>
            <textarea name="staff_note" class="form-control" rows="2"></textarea>
        </div>
    </div>

    <div class="text-end border-top pt-3">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-check-circle me-1"></i> Submit Return Request
        </button>
    </div>
</form>

<script>
    function calculateRow(input) {
        let row = $(input).closest('tr');
        let qty = parseFloat($(input).val()) || 0;
        let price = parseFloat(row.find('.unit-price').val()) || 0;
        let maxQty = parseFloat($(input).attr('max'));
        if (qty > maxQty) {
            if(typeof showFloatingAlert === 'function') {
                showFloatingAlert('danger', @json(__('Cannot return more than purchased quantity (:qty)')).replace(':qty', maxQty));
            }
            $(input).val(maxQty);
            qty = maxQty;
        }

        let total = qty * price;
        row.find('.row-total').text(total.toFixed(2));

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        $('.row-total').each(function() {
            grandTotal += parseFloat($(this).text()) || 0;
        });
        $('#grandTotalDisplay').text(grandTotal.toFixed(2));
    }
</script>
