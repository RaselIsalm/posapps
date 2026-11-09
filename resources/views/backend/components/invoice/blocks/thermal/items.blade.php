@php
    $items = $items ?? ($invoice['items'] ?? []);
@endphp
<div data-print-block="items">
    <div style="border-bottom: 1.5px solid #000; margin-bottom: 5px;"></div>
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5px; font-size: 11px;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 5px 0; font-weight: bold; text-transform: uppercase; width: 50%; white-space: nowrap;">{{ \App\Helpers\InvoiceHelper::t('item') }}</th>
                <th style="text-align: right; padding: 5px 0; font-weight: bold; text-transform: uppercase; width: 20%; white-space: nowrap;">
                    {{ \App\Helpers\InvoiceHelper::t('qty') }}</th>
                <th style="text-align: right; padding: 5px 0; font-weight: bold; text-transform: uppercase; width: 30%; white-space: nowrap;">
                    {{ \App\Helpers\InvoiceHelper::t('total') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="border-bottom: 1.5px solid #000;"></td>
            </tr>
            @forelse ($items as $item)
                <tr>
                    <td style="text-align: left; padding: 5px 0; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ $item['title'] ?? \App\Helpers\InvoiceHelper::t('item') }}
                        @if (! empty($item['meta']))
                            <div style="font-size: 9px; color: #555;">{{ $item['meta'] }}</div>
                        @endif
                        @if (! empty($item['warranty']) && ($item['warranty'] ?? \App\Helpers\InvoiceHelper::t('na')) !== \App\Helpers\InvoiceHelper::t('na') && ($item['warranty'] ?? '') !== 'N/A')
                            <div style="font-size: 9px; color: #555; margin-top: 4px;">
                                <div style="font-weight: 700; color: #000;">{{ \App\Helpers\InvoiceHelper::t('warranty') }}:</div>
                                <div>• {{ $item['warranty'] }}</div>
                            </div>
                        @endif
                    </td>
                    <td style="text-align: right; padding: 5px 0; white-space: nowrap;">{{ $item['qty'] ?? 0 }}</td>
                    <td style="text-align: right; padding: 5px 0; white-space: nowrap;">
                        {{ \App\Helpers\CurrencyHelper::format($item['total'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 8px 0;">{{ \App\Helpers\InvoiceHelper::t('items_empty') }}</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3" style="border-bottom: 1.5px solid #000; padding-top: 5px;"></td>
            </tr>
        </tbody>
    </table>
</div>
