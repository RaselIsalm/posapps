@php
    $items = $items ?? ($invoice['items'] ?? []);
@endphp
<div data-print-block="items" class="table-responsive mb-2">
    <table class="table table-borderless align-middle"
        style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
        <thead>
            <tr style="font-size: 10px; letter-spacing: 1px;" class="text-uppercase text-muted">
                <th class="py-2 px-1">{{ \App\Helpers\InvoiceHelper::t('description') }}</th>
                <th class="py-2 px-1 text-center" width="10%">{{ \App\Helpers\InvoiceHelper::t('qty') }}</th>
                <th class="py-2 px-1 text-end" width="15%">{{ \App\Helpers\InvoiceHelper::t('unit_price') }}</th>
                <th class="py-2 px-1 text-end" width="15%">{{ \App\Helpers\InvoiceHelper::t('total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr style="border-top: 1px solid #f1f3f5;">
                    <td class="py-2 px-1">
                        <span class="fw-bold text-dark d-block"
                            style="font-size: 12px;">{{ $item['title'] ?? \App\Helpers\InvoiceHelper::t('item') }}</span>
                        @if (! empty($item['meta']))
                            <span class="text-muted" style="font-size: 10px;">{{ $item['meta'] }}</span>
                        @endif
                    </td>
                    <td class="py-2 px-1 text-center fw-bold" style="font-size: 12px;">{{ $item['qty'] ?? 0 }}
                    </td>
                    <td class="py-2 px-1 text-end text-muted" style="font-size: 12px;">
                        {{ \App\Helpers\CurrencyHelper::format($item['unit_price'] ?? 0, 2) }}</td>
                    <td class="py-2 px-1 text-end fw-bold text-dark" style="font-size: 12px;">
                        {{ \App\Helpers\CurrencyHelper::format($item['total'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">{{ \App\Helpers\InvoiceHelper::t('items_empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
