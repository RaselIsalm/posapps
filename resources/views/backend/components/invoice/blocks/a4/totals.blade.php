@php
    $totalDiscount = $totalDiscount ?? \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $receivedDisplay = $receivedDisplay ?? max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = $changeDisplay ?? (float) ($invoice['change_amount'] ?? 0);
    $charges = $charges ?? ($invoice['charges'] ?? []);
@endphp
<div data-print-block="totals" class="row mb-2">
    <div class="col-7"></div>
    <div class="col-5">
        <table class="table table-sm table-borderless text-end mb-0" style="font-size: 12px;">
            <tr>
                <td class="pe-4 py-1" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('subtotal') }}</td>
                <td class="fw-bold py-1" style="color: #000;">
                    {{ \App\Helpers\CurrencyHelper::format($invoice['sub_total'] ?? 0, 2) }}</td>
            </tr>
            @if ($totalDiscount > 0)
                <tr>
                    <td class="pe-4 py-1" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('discount') }}</td>
                    <td class="py-1" style="color: #000;">(-)
                        {{ \App\Helpers\CurrencyHelper::format($totalDiscount, 2) }}</td>
                </tr>
            @endif
            @foreach ($charges as $charge)
                <tr>
                    <td class="pe-4 py-1" style="color: #000;">{{ $charge['label'] ?? \App\Helpers\InvoiceHelper::t('charge') }}</td>
                    <td class="py-1" style="color: #000;">(+)
                        {{ \App\Helpers\CurrencyHelper::format($charge['amount'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
            @if (($invoice['tax_amount'] ?? 0) > 0)
                <tr>
                    <td class="pe-4 py-1" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('tax') }} ({{ $invoice['tax_percent'] ?? 0 }}%)</td>
                    <td class="py-1" style="color: #000;">(+)
                        {{ \App\Helpers\CurrencyHelper::format($invoice['tax_amount'] ?? 0, 2) }}</td>
                </tr>
            @endif
            <tr style="border-top: 1px solid #ced4da; border-bottom: 1px solid #ced4da;">
                <td class="fw-bold pe-4 py-2" style="font-size: 13px; color: #000;">{{ \App\Helpers\InvoiceHelper::t('grand_total') }}</td>
                <td class="fw-bold py-2"
                    style="font-size: 15px; color: #000 !important;">
                    {{ \App\Helpers\CurrencyHelper::format($invoice['grand_total'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="pe-4 py-1" style="font-size: 11px; color: #000;">{{ \App\Helpers\InvoiceHelper::t('customer_paid') }}</td>
                <td class="fw-bold py-1" style="font-size: 12px; color: #000;">
                    {{ \App\Helpers\CurrencyHelper::format($receivedDisplay, 2) }}</td>
            </tr>
            @if ($changeDisplay > 0)
                <tr>
                    <td class="fw-bold pe-4 py-1" style="font-size: 11px; color: #000;">{{ \App\Helpers\InvoiceHelper::t('change') }}</td>
                    <td class="fw-bold py-1" style="font-size: 12px; color: #000;">
                        {{ \App\Helpers\CurrencyHelper::format($changeDisplay, 2) }}</td>
                </tr>
            @endif
            @if (($invoice['due_amount'] ?? 0) > 0)
                <tr>
                    <td class="fw-bold pe-4 py-1" style="font-size: 12px; color: #000;">
                        {{ \App\Helpers\InvoiceHelper::t('due_balance') }}</td>
                    <td class="fw-bold py-1" style="font-size: 13px; color: #000;">
                        {{ \App\Helpers\CurrencyHelper::format($invoice['due_amount'] ?? 0, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>
</div>
