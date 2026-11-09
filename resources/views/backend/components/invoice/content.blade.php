@php
    /** @var array $invoice */
    $invoice = $invoice ?? [];
    $company = $invoice['company'] ?? [];
    $from = $invoice['from'] ?? [];
    $billTo = $invoice['bill_to'] ?? [];
    $items = $invoice['items'] ?? [];
    $discounts = $invoice['discounts'] ?? [];
    $charges = $invoice['charges'] ?? [];
    $payments = $invoice['payments'] ?? [];
    $infoBlocks = $invoice['info_blocks'] ?? [];
    $terms = $invoice['terms'] ?? [];
    $status = strtolower($invoice['status'] ?? '');
    $statusClass = match (true) {
        in_array($status, ['paid', 'received', 'delivered', 'completed', 'sale_generated', 'approved'], true) => 'bg-success',
        in_array($status, ['partial', 'pending', 'ordered', 'shipped', 'sent', 'request'], true) => 'bg-warning text-dark',
        in_array($status, ['cancelled', 'expired'], true) => 'bg-danger',
        in_array($status, ['purchase_generated', 'info'], true) => 'bg-primary',
        default => 'bg-secondary',
    };
    $headerColor = $invoice['header_color'] ?? 'var(--primary-color, #308e87)';
    $totalDiscount = \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $totalCharges = \App\Helpers\InvoiceHelper::totalCharges($invoice);
    $footerNote = $invoice['footer_note']
        ?? \App\Helpers\InvoiceHelper::defaultFooterNote($company['name'] ?? null, $company['phone'] ?? null);
    $receivedDisplay = max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = (float) ($invoice['change_amount'] ?? 0);
@endphp

<div id="invoice_content" data-print-title="{{ $invoice['print_title'] ?? __('Print Invoice') }}"
    style="font-family: 'Roboto', sans-serif; background: #fff; position: relative;">

    <div class="d-flex justify-content-between align-items-center p-3 text-white rounded-top"
        style="background-color: {{ $headerColor }};">
        <h5 class="mb-0 fw-bold">{{ $invoice['modal_title'] ?? \App\Helpers\InvoiceHelper::t('invoice') }}</h5>
        <button type="button" class="btn-close btn-close-white" onclick="forceCloseInvoiceModal()"></button>
    </div>

    <div class="p-4">
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                    {{ $from['label'] ?? \App\Helpers\InvoiceHelper::t('from') }}:</h6>
                <h5 class="fw-bold text-dark mb-1 notranslate" translate="no" lang="und">{{ $from['name'] ?? '' }}</h5>
                @if (! empty($from['bin_number']))
                    <p class="mb-0 text-muted small">BIN: {{ $from['bin_number'] }}</p>
                @endif
                @if (! empty($from['address']))
                    <p class="mb-0 text-muted small">{{ $from['address'] }}</p>
                @endif
                @if (! empty($from['phone']))
                    <p class="mb-0 text-muted small">{{ \App\Helpers\InvoiceHelper::t('phone') }}: {{ $from['phone'] }}</p>
                @endif
                @if (! empty($from['telephone']))
                    <p class="mb-0 text-muted small">{{ \App\Helpers\InvoiceHelper::t('tel') }}: {{ $from['telephone'] }}</p>
                @endif
                @if (! empty($from['website']))
                    <p class="mb-0 text-muted small">{{ $from['website'] }}</p>
                @endif
            </div>
            <div class="col-6 text-end">
                <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                    {{ $billTo['label'] ?? \App\Helpers\InvoiceHelper::t('to') }}:</h6>
                <h5 class="fw-bold text-dark mb-1">{{ $billTo['name'] ?? '' }}</h5>
                @if (! empty($billTo['address']))
                    <p class="mb-0 text-muted small">{{ $billTo['address'] }}</p>
                @endif
                @if (! empty($billTo['phone']))
                    <p class="mb-0 text-muted small">{{ \App\Helpers\InvoiceHelper::t('phone') }}: {{ $billTo['phone'] }}</p>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded"
            style="background-color: #eef2f7;">
            <div>
                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">{{ \App\Helpers\InvoiceHelper::t('invoice_no') }}</span>
                <span class="fw-bold text-primary">{{ $invoice['number'] ?? '' }}</span>
            </div>
            <div class="text-center">
                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">{{ \App\Helpers\InvoiceHelper::t('date') }}</span>
                <span class="fw-bold text-dark">{{ $invoice['date_short'] ?? '' }}</span>
            </div>
            <div class="text-end">
                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 11px;">{{ \App\Helpers\InvoiceHelper::t('status') }}</span>
                @if (! empty($invoice['status']))
                    <span class="badge {{ $statusClass }} px-3 py-1">{{ \App\Helpers\InvoiceHelper::statusLabel($invoice['status']) }}</span>
                @endif
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="bg-light text-secondary">
                    <tr style="font-size: 12px;">
                        <th class="py-2 text-center" width="5%">#</th>
                        <th class="py-2" width="45%">{{ \App\Helpers\InvoiceHelper::t('product') }}</th>
                        <th class="py-2 text-center" width="10%">{{ \App\Helpers\InvoiceHelper::t('qty') }}</th>
                        <th class="py-2 text-end" width="20%">{{ \App\Helpers\InvoiceHelper::t('unit_price') }}</th>
                        <th class="py-2 text-end" width="20%">{{ \App\Helpers\InvoiceHelper::t('total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $key => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $key + 1 }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $item['title'] ?? \App\Helpers\InvoiceHelper::t('item') }}</span>
                                @if (! empty($item['meta']))
                                    <div class="mt-1" style="font-size: 0.85rem;">
                                        <span class="text-muted">{{ $item['meta'] }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $item['qty'] ?? 0 }}</td>
                            <td class="text-end text-muted">
                                {{ \App\Helpers\CurrencyHelper::format($item['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">
                                {{ \App\Helpers\CurrencyHelper::format($item['total'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">{{ \App\Helpers\InvoiceHelper::t('items_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-6">
                @if (($invoice['show_payment_history'] ?? true) && count($payments) > 0)
                    <h6 class="fw-bold border-bottom pb-2 mb-3">{{ \App\Helpers\InvoiceHelper::t('payment_history') }}</h6>
                    <table class="table table-sm table-borderless small">
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="text-muted">{{ $payment['date'] ?? '' }}</td>
                                <td class="fw-bold">{{ \App\Helpers\InvoiceHelper::paymentMethodLabel($payment['method'] ?? '') }}</td>
                                <td class="text-end fw-bold text-dark">
                                    {{ \App\Helpers\CurrencyHelper::format($payment['received'] ?? $payment['amount'] ?? 0, 2) }}
                                    @if (($payment['change'] ?? 0) > 0)
                                        <div class="small text-success fw-normal">{{ \App\Helpers\InvoiceHelper::t('change') }}: {{ \App\Helpers\CurrencyHelper::format($payment['change'], 2) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                @foreach ($infoBlocks as $block)
                    <h6 class="fw-bold border-bottom pb-2 mb-3">{{ $block['title'] ?? \App\Helpers\InvoiceHelper::t('information') }}</h6>
                    <table class="table table-sm table-borderless small">
                        @foreach ($block['rows'] ?? [] as $row)
                            <tr>
                                <td class="text-muted">{{ $row['label'] ?? '' }}:</td>
                                <td class="fw-bold">{{ $row['value'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endforeach

                @if (! empty($invoice['notes']))
                    <h6 class="fw-bold border-bottom pb-2 mb-2">{{ \App\Helpers\InvoiceHelper::t('note') }}</h6>
                    <p class="small text-muted">{{ $invoice['notes'] }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless text-end">
                    <tr>
                        <td class="fw-bold text-muted">{{ \App\Helpers\InvoiceHelper::t('subtotal') }}:</td>
                        <td class="fw-bold">
                            {{ \App\Helpers\CurrencyHelper::format($invoice['sub_total'] ?? 0, 2) }}</td>
                    </tr>
                    @foreach ($discounts as $discount)
                        <tr>
                            <td class="text-muted">{{ $discount['label'] ?? \App\Helpers\InvoiceHelper::t('discount') }}:</td>
                            <td class="text-danger">(-)
                                {{ \App\Helpers\CurrencyHelper::format($discount['amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach ($charges as $charge)
                        <tr>
                            <td class="text-muted">{{ $charge['label'] ?? \App\Helpers\InvoiceHelper::t('charge') }}:</td>
                            <td class="text-muted">(+)
                                {{ \App\Helpers\CurrencyHelper::format($charge['amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    @if (($invoice['tax_amount'] ?? 0) > 0 || ($invoice['tax_percent'] ?? 0) > 0)
                        <tr>
                            <td class="text-muted">{{ \App\Helpers\InvoiceHelper::t('tax') }}{{ ($invoice['tax_percent'] ?? 0) > 0 ? ' ('.$invoice['tax_percent'].'%)' : '' }}:</td>
                            <td class="text-muted">(+)
                                {{ \App\Helpers\CurrencyHelper::format($invoice['tax_amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="2" class="border-bottom"></td>
                    </tr>
                    <tr>
                        <td class="fw-bold align-middle h6" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('grand_total') }}:</td>
                        <td class="fw-bold fs-5" style="color: #000;">
                            {{ \App\Helpers\CurrencyHelper::format($invoice['grand_total'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="small fw-bold" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('customer_paid') }}:</td>
                        <td class="small fw-bold" style="color: #000;">
                            {{ \App\Helpers\CurrencyHelper::format($receivedDisplay, 2) }}</td>
                    </tr>
                    @if ($changeDisplay > 0)
                        <tr>
                            <td class="small fw-bold" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('change') }}:</td>
                            <td class="small fw-bold" style="color: #000;">
                                {{ \App\Helpers\CurrencyHelper::format($changeDisplay, 2) }}</td>
                        </tr>
                    @endif
                    @if (($invoice['due_amount'] ?? 0) > 0)
                        <tr>
                            <td class="small fw-bold" style="color: #000;">{{ \App\Helpers\InvoiceHelper::t('due_balance') }}:</td>
                            <td class="small fw-bold" style="color: #000;">
                                {{ \App\Helpers\CurrencyHelper::format($invoice['due_amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        @if (count($terms) > 0)
            <div class="mt-4 w-50">
                <h6 class="fw-bold mb-3"
                    style="background-color: #555 !important; color: #fff !important; padding: 5px 10px; font-size: 14px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    {{ \App\Helpers\InvoiceHelper::t('terms') }}
                </h6>
                <ul class="small text-muted ps-3 mb-0" style="list-style-type: disc;">
                    @foreach ($terms as $term)
                        <li class="mb-1" style="color: #000 !important;">{{ $term }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

@include('backend.components.invoice.partials.a4', compact('invoice', 'company', 'billTo', 'from', 'items', 'discounts', 'charges', 'terms', 'headerColor', 'totalDiscount', 'totalCharges', 'footerNote', 'receivedDisplay', 'changeDisplay'))

@if ($invoice['show_thermal'] ?? false)
    @include('backend.components.invoice.partials.thermal', compact('invoice', 'company', 'billTo', 'items', 'totalDiscount', 'terms'))
@endif

<div class="modal-footer border-top-0 bg-light rounded-bottom p-3 d-print-none justify-content-end">
    <button type="button" class="btn btn-danger px-4" onclick="forceCloseInvoiceModal()">{{ \App\Helpers\InvoiceHelper::t('close') }}</button>
    <button type="button" class="btn btn-primary px-4" onclick="printInvoiceSection()">
        <i class="fas fa-print me-2"></i> {{ \App\Helpers\InvoiceHelper::t('print') }}
    </button>
    @if ($invoice['show_thermal'] ?? false)
        @include('backend.components.invoice.partials.thermal_actions')
    @endif
</div>
