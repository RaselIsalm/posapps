@php
    $cash = \App\Helpers\InvoiceHelper::saleCashBreakdown($sale);
    $receivedDisplay = max((float) $cash['received_amount'], (float) $cash['paid_amount']);
    $changeDisplay = (float) $cash['change_amount'];
@endphp
<tr>
    <td style="padding: 8px 0; font-size: 14px; color: #10b981;">{{ __('Customer Paid:') }}</td>
    <td style="padding: 8px 0; font-size: 14px; text-align: right; color: #10b981;">{{ \App\Helpers\CurrencyHelper::format($receivedDisplay) }}</td>
</tr>
@if ($changeDisplay > 0)
<tr>
    <td style="padding: 8px 0; font-size: 14px; color: #0d9488;">{{ __('Change:') }}</td>
    <td style="padding: 8px 0; font-size: 14px; text-align: right; color: #0d9488;">{{ \App\Helpers\CurrencyHelper::format($changeDisplay) }}</td>
</tr>
@endif
