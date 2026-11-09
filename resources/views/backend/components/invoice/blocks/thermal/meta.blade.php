@php
    $billTo = $billTo ?? ($invoice['bill_to'] ?? []);
    $company = $company ?? ($invoice['company'] ?? []);
@endphp
<div data-print-block="meta" style="margin-bottom: 10px;">
    <table style="width: 100%; font-size: 11px;">
        <tr>
            <td style="width: 30%; padding-bottom: 2px;">{{ $invoice['thermal_number_label'] ?? \App\Helpers\InvoiceHelper::t('invoice') }}</td>
            <td style="text-align: right; font-weight: bold; padding-bottom: 2px;">
                {{ $invoice['number'] ?? '' }}</td>
        </tr>
        @if (! empty($invoice['date_long']) || ! empty($invoice['date_short']))
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('date') }}</td>
                <td style="text-align: right; padding-bottom: 2px;">
                    {{ $invoice['date_long'] ?? $invoice['date_short'] }}</td>
            </tr>
        @endif
        @if (! empty($billTo['name']))
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('customer') }}</td>
                <td style="text-align: right; padding-bottom: 2px;">{{ $billTo['name'] }}</td>
            </tr>
        @endif
        @if (! empty($billTo['phone']))
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('phone') }}</td>
                <td style="text-align: right; padding-bottom: 2px;">{{ $billTo['phone'] }}</td>
            </tr>
        @endif
        @if (! empty($invoice['sold_by']))
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('sold_by') }}</td>
                <td style="text-align: right; padding-bottom: 2px;">{{ $invoice['sold_by'] }}</td>
            </tr>
        @endif
        @if (! empty($company['branch_name'] ?? ($invoice['company']['branch_name'] ?? null)))
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('branch') }}</td>
                <td class="notranslate" translate="no" lang="und" style="text-align: right; padding-bottom: 2px;">
                    {{ $company['branch_name'] ?? $invoice['company']['branch_name'] }}</td>
            </tr>
        @endif
        @php
            $paymentDisplay = \App\Helpers\InvoiceHelper::invoicePaymentMethodDisplay($invoice);
        @endphp
        @if ($paymentDisplay !== '')
            <tr>
                <td style="padding-bottom: 2px;">{{ \App\Helpers\InvoiceHelper::t('payment') }}</td>
                <td style="text-align: right; padding-bottom: 2px; text-transform: uppercase;">
                    {{ $paymentDisplay }}
                </td>
            </tr>
        @endif
        @foreach ($invoice['thermal_extra_rows'] ?? [] as $row)
            <tr>
                <td style="padding-bottom: 2px;">{{ $row['label'] ?? '' }}</td>
                <td style="text-align: right; padding-bottom: 2px;">{{ $row['value'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>
</div>
