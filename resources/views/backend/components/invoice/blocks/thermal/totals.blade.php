@php
    $totalDiscount = $totalDiscount ?? \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $receivedDisplay = $receivedDisplay ?? max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = $changeDisplay ?? (float) ($invoice['change_amount'] ?? 0);
@endphp
<div data-print-block="totals" style="margin-bottom: 10px;">
    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
        <tr>
            <td style="text-align: left; padding: 5px 0;">{{ \App\Helpers\InvoiceHelper::t('subtotal') }}</td>
            <td style="text-align: right; padding: 5px 0;">
                {{ \App\Helpers\CurrencyHelper::format($invoice['sub_total'] ?? 0, 2) }}</td>
        </tr>
        @if ($totalDiscount > 0)
            <tr>
                <td style="text-align: left; padding: 2px 0;">{{ \App\Helpers\InvoiceHelper::t('discount') }}</td>
                <td style="text-align: right; padding: 2px 0;">(-)
                    {{ \App\Helpers\CurrencyHelper::format($totalDiscount, 2) }}</td>
            </tr>
        @endif
        @foreach ($invoice['charges'] ?? [] as $charge)
            <tr>
                <td style="text-align: left; padding: 2px 0;">{{ $charge['label'] ?? \App\Helpers\InvoiceHelper::t('charge') }}</td>
                <td style="text-align: right; padding: 2px 0;">(+)
                    {{ \App\Helpers\CurrencyHelper::format($charge['amount'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        @if (($invoice['tax_amount'] ?? 0) > 0)
            @php
                $taxPercent = (float) ($invoice['tax_percent'] ?? 0);
                $taxLabel = $taxPercent > 0 ? \App\Helpers\InvoiceHelper::t('tax').' ('.$taxPercent.'%)' : \App\Helpers\InvoiceHelper::t('tax');
            @endphp
            <tr>
                <td style="text-align: left; padding: 2px 0;">{{ $taxLabel }}</td>
                <td style="text-align: right; padding: 2px 0;">(+)
                    {{ \App\Helpers\CurrencyHelper::format($invoice['tax_amount'] ?? 0, 2) }}</td>
            </tr>
        @endif
    </table>

    <div style="margin: 7px 0 6px; padding: 7px 6px; border-top: 2px solid #000; border-bottom: 2px solid #000;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 800; font-size: 13px; letter-spacing: 0.6px;">{{ \App\Helpers\InvoiceHelper::t('grand_total') }}</span>
            <span style="font-weight: 800; font-size: 14px;">{{ \App\Helpers\CurrencyHelper::format($invoice['grand_total'] ?? 0, 2) }}</span>
        </div>
    </div>

    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
        @php
            $paidMethodLabel = \App\Helpers\InvoiceHelper::paymentMethodLabel($invoice['payment_method'] ?? '');
        @endphp
        <tr>
            <td style="text-align: left; padding: 5px 0;">{{ \App\Helpers\InvoiceHelper::t('customer_paid') }}
                @if ($paidMethodLabel !== '')
                    ({{ mb_strtolower($paidMethodLabel) }})
                @endif
            </td>
            <td style="text-align: right; padding: 5px 0;">
                {{ \App\Helpers\CurrencyHelper::format($receivedDisplay, 2) }}</td>
        </tr>
        @if ($changeDisplay > 0)
            <tr>
                <td style="text-align: left; padding: 5px 0; font-weight: 700;">{{ \App\Helpers\InvoiceHelper::t('change') }}</td>
                <td style="text-align: right; padding: 5px 0; font-weight: 700;">
                    {{ \App\Helpers\CurrencyHelper::format($changeDisplay, 2) }}</td>
            </tr>
        @endif
        @if (($invoice['due_amount'] ?? 0) > 0)
            <tr>
                <td colspan="2" style="padding: 6px 0 2px 0;">
                    <div style="border: 2px solid #000; padding: 6px 8px; box-sizing: border-box;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; font-size: 12px; color: #000;">{{ \App\Helpers\InvoiceHelper::t('due') }}</span>
                            <span style="font-weight: 800; font-size: 13px; color: #000;">{{ \App\Helpers\CurrencyHelper::format($invoice['due_amount'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="2" style="border-bottom: 1.5px solid #000; padding-top: 5px;"></td>
        </tr>
    </table>
</div>
