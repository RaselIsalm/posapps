<form action="{{ route('sale-return.store') }}" method="POST" id="saleReturnForm">
    @csrf
    <input type="hidden" name="sale_id" value="{{ $sale->id }}">

    {{-- Invoice Info Header --}}
    <div class="alert alert-soft-primary d-flex justify-content-between align-items-center p-3 mb-3">
        <div>
            <small class="text-muted d-block fw-bold">INVOICE</small>
            <span class="fw-bold text-primary">{{ $sale->invoice_no }}</span>
        </div>
        <div class="text-end">
            <small class="text-muted d-block fw-bold">CUSTOMER</small>
            <span class="fw-bold">{{ $sale->customer->name ?? 'Walk-in' }}</span>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="table-responsive border rounded mb-3">
        <table class="table table-sm table-striped mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3">Product</th>
                    <th class="text-center">Sold</th>
                    <th class="text-center" width="150">Return Qty</th>
                    <th class="text-end pe-3">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->productSales as $item)
                @php
                    $soldImeis = \App\Models\ProductImei::where('sale_id', $sale->id)
                                ->where('product_id', $item->product_id)
                                ->get();
                @endphp
                <tr>
                    <td class="ps-3">
                        <span class="fw-bold text-dark small">{{ $item->product->product_title }}</span>
                        
                        @if($soldImeis->count() > 0)
                            <div class="mt-1">
                                <label class="text-primary fw-bold" style="font-size: 10px;">SELECT RETURNED IMEI:</label>
                                <select name="imei_number[]" class="form-select form-select-sm imei-select" multiple>
                                    @foreach($soldImeis as $imei)
                                        <option value="{{ $imei->imei_number }}">{{ $imei->imei_number }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block" style="font-size: 9px;">Hold Ctrl to select multiple</small>
                            </div>
                        @else
                            <input type="hidden" name="imei_number[]" value="">
                        @endif

                        {{-- Hidden Inputs --}}
                        <input type="hidden" name="return_items[]" value="{{ $item->product_id }}">
                        <input type="hidden" name="variant_id[]" value="{{ $item->variant_id ?? null }}">
                        <input type="hidden" name="unit_price[]" value="{{ $item->unit_price }}" class="unit-price">
                    </td>
                    <td class="text-center small">{{ $item->qty }}</td>
                    <td>
                        <input type="number" name="qty[]" class="form-control form-control-sm text-center return-qty" 
                            value="0" min="0" max="{{ $item->qty }}" data-max="{{ $item->qty }}" 
                            @if($soldImeis->count() > 0) readonly @endif> 
                    </td>
                    <td class="text-end pe-3 fw-bold small row-total">0.00</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Total & Notes --}}
    <div class="row g-2">
        <div class="col-md-12 text-end mb-2">
            <h6 class="fw-bold text-dark">Total Refund: <span id="display_grand_total" class="text-primary">0.00</span></h6>
        </div>
        <div class="col-md-6 col-12">
            <label class="form-label small fw-bold">Return Note</label>
            <textarea name="return_note" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="col-md-6 col-12">
            <label class="form-label small fw-bold">Staff Note</label>
            <textarea name="staff_note" class="form-control form-control-sm" rows="2"></textarea>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0 mt-3 border-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-sm px-4">Confirm Return</button>
    </div>
</form>

<script>
    // IMEI Select logic
    $('.imei-select').on('change', function() {
        let selectedCount = $(this).find('option:selected').length;
        let row = $(this).closest('tr');
        row.find('.return-qty').val(selectedCount).trigger('input');
    });

    // Qty Change Logic
    $('.return-qty').on('input', function() {
        let row = $(this).closest('tr');
        let price = parseFloat(row.find('.unit-price').val());
        let qty = parseFloat($(this).val()) || 0;
        let maxQty = parseFloat($(this).data('max'));

        if(qty > maxQty) {
            if(typeof showFloatingAlert === 'function') {
                showFloatingAlert('danger', @json(__('Cannot return more than sold quantity!')));
            }
            $(this).val(maxQty);
            qty = maxQty;
        }
        if(qty < 0) { $(this).val(0); qty = 0; }

        let total = price * qty;
        row.find('.row-total').text(total.toFixed(2));

        calculateGrandTotal();
    });

    function calculateGrandTotal() {
        let grandTotal = 0;
        $('.row-total').each(function() { 
            grandTotal += parseFloat($(this).text()) || 0; 
        });
        $('#display_grand_total').text(grandTotal.toFixed(2));
    }
</script>