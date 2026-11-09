@php
    $billTo = $billTo ?? ($invoice['bill_to'] ?? []);
    $from = $from ?? ($invoice['from'] ?? []);
@endphp
<div data-print-block="parties" class="row mb-2">
    <div class="col-6 pe-2">
        <div class="p-2 h-100 rounded"
            style="background-color: #f1f3f5; border: 1px solid #e9ecef; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 1px;">
                {{ strtoupper($billTo['label'] ?? \App\Helpers\InvoiceHelper::t('bill_to')) }}</p>
            <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">{{ $billTo['name'] ?? '' }}</h6>
            <p class="mb-0 text-muted" style="font-size: 11px;">
                @if (! empty($billTo['phone']))
                    <i class="fas fa-mobile-alt" style="font-size: 9px;"></i> {{ $billTo['phone'] }}
                @endif
            </p>
        </div>
    </div>
    <div class="col-6 ps-2">
        <div class="p-2 h-100 rounded"
            style="background-color: #f1f3f5; border: 1px solid #e9ecef; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
            <p class="text-uppercase text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 1px;">
                {{ strtoupper($from['label'] ?? \App\Helpers\InvoiceHelper::t('from')) }}</p>
            <h6 class="fw-bold text-dark mb-1 notranslate" translate="no" lang="und" style="font-size: 13px;">{{ $from['name'] ?? '' }}</h6>
            @if (! empty($from['bin_number']))
                <p class="mb-0 text-muted" style="font-size: 10px;">BIN: {{ $from['bin_number'] }}</p>
            @endif
            @if (! empty($from['address']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ $from['address'] }}</p>
            @endif
            @if (! empty($from['phone']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ \App\Helpers\InvoiceHelper::t('phone') }}: {{ $from['phone'] }}</p>
            @endif
            @if (! empty($from['telephone']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ \App\Helpers\InvoiceHelper::t('tel') }}: {{ $from['telephone'] }}</p>
            @endif
            @if (! empty($from['website']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ $from['website'] }}</p>
            @endif
            <table class="table table-sm table-borderless mb-0 mt-1"
                style="font-size: 11px; background-color: transparent !important;">
                @php
                    $paymentDisplay = \App\Helpers\InvoiceHelper::invoicePaymentMethodDisplay($invoice);
                @endphp
                @if ($paymentDisplay !== '')
                    <tr style="background-color: transparent !important;">
                        <td class="text-muted p-0 pb-1" style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('method') }}</td>
                        <td class="text-dark fw-bold text-end p-0 pb-1 text-uppercase"
                            style="background-color: transparent !important;">
                            {{ $paymentDisplay }}</td>
                    </tr>
                @endif
                @if (! empty($invoice['sold_by']))
                    <tr style="background-color: transparent !important;">
                        <td class="text-muted p-0" style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('sold_by') }}</td>
                        <td class="text-dark fw-bold text-end p-0"
                            style="background-color: transparent !important;">{{ $invoice['sold_by'] }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</div>
