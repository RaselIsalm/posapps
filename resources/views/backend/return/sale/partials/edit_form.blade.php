<form action="{{ route('sale-return.update', $return->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Info Header --}}
    <div class="alert alert-soft-warning d-flex justify-content-between align-items-center p-3 mb-3">
        <div>
            <small class="text-muted d-block fw-bold">RETURN REF</small>
            <span class="fw-bold text-dark">{{ $return->reference_no }}</span>
        </div>
        <div class="text-end">
            <small class="text-muted d-block fw-bold">SALE REF</small>
            <span class="fw-bold text-primary">{{ $return->sale->invoice_no ?? 'N/A' }}</span>
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
                @foreach ($return->sale->productSales as $item)
                    @php
                        $existingReturn = $return->details
                            ->where('product_id', $item->product_id)
                            ->where('variant_id', $item->variant_id)
                            ->first();
                        
                        $currentReturnQty = $existingReturn ? $existingReturn->qty : 0;
                        
                        $soldImeis = \App\Models\ProductImei::where('sale_id', $return->sale_id)
                                    ->where('product_id', $item->product_id)
                                    ->get();

                        $returnedImeiArray = $existingReturn && $existingReturn->imei_number 
                                            ? explode(',', $existingReturn->imei_number) 
                                            : [];
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <span class="fw-bold small">{{ $item->product->product_title }}</span>
                            @if ($item->variant)
                                <br><small class="text-muted">{{ $item->variant->name }}</small>
                            @endif

                            {{-- IMEI Selection --}}
                            @if($soldImeis->count() > 0)
                                <div class="mt-1">
                                    <label class="text-warning fw-bold" style="font-size: 10px;">RETURNED IMEI:</label>
                                    <select name="imei_number[]" class="form-select form-select-sm imei-select" multiple>
                                        @foreach($soldImeis as $imei)
                                            <option value="{{ $imei->imei_number }}" 
                                                {{ in_array($imei->imei_number, $returnedImeiArray) ? 'selected' : '' }}>
                                                {{ $imei->imei_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block" style="font-size: 9px;">Hold Ctrl to change selection</small>
                                </div>
                            @else
                                <input type="hidden" name="imei_number[]" value="">
                            @endif

                            <input type="hidden" name="return_items[]" value="{{ $item->product_id }}">
                            <input type="hidden" name="variant_id[]" value="{{ $item->variant_id ?? null }}">
                            <input type="hidden" name="unit_price[]" value="{{ $item->unit_price }}" class="unit-price">
                        </td>
                        <td class="text-center small">{{ $item->qty }}</td>
                        <td>
                            <input type="number" name="qty[]"
                                class="form-control form-control-sm text-center return-qty"
                                value="{{ $currentReturnQty }}" min="0" max="{{ $item->qty }}"
                                data-max="{{ $item->qty }}"
                                @if($soldImeis->count() > 0) readonly @endif>
                        </td>
                        <td class="text-end pe-3 fw-bold small row-total">
                            {{ \App\Helpers\CurrencyHelper::format($currentReturnQty * $item->unit_price, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="text-end mb-3">
        <h6 class="fw-bold">Total Refund: <span id="edit_grand_total"
                class="text-primary">{{ \App\Helpers\CurrencyHelper::format($return->grand_total, 2) }}</span></h6>
    </div>

    <div class="row g-2">
        <div class="col-md-6 col-12">
            <label class="small fw-bold">Return Note</label>
            <textarea name="return_note" class="form-control form-control-sm">{{ $return->return_note }}</textarea>
        </div>
        <div class="col-md-6 col-12">
            <label class="small fw-bold">Staff Note</label>
            <textarea name="staff_note" class="form-control form-control-sm">{{ $return->staff_note }}</textarea>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0 mt-3 border-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-warning btn-sm px-4 text-white">Update Return</button>
    </div>
</form>

<script>
    // IMEI Change Logic - Update Qty automatically
    $('.imei-select').on('change', function() {
        let selectedCount = $(this).find('option:selected').length;
        let row = $(this).closest('tr');
        row.find('.return-qty').val(selectedCount).trigger('input');
    });

    // Qty Calculation Logic
    $('.return-qty').on('input', function() {
        let row = $(this).closest('tr');
        let price = parseFloat(row.find('.unit-price').val());
        let qty = parseFloat($(this).val()) || 0;
        let maxQty = parseFloat($(this).data('max'));

        if (qty > maxQty) {
            $(this).val(maxQty);
            qty = maxQty;
            if(typeof showFloatingAlert === 'function') {
                showFloatingAlert('danger', @json(__('Limit Exceeded!')));
            }
        }
        if (qty < 0) {
            $(this).val(0);
            qty = 0;
        }

        row.find('.row-total').text((price * qty).toFixed(2));

        let grandTotal = 0;
        $('.row-total').each(function() {
            grandTotal += parseFloat($(this).text()) || 0;
        });
        $('#edit_grand_total').text(grandTotal.toFixed(2));
    });
</script>