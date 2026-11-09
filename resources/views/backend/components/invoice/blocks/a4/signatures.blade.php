@php
    $footerNote = $footerNote
        ?? ($invoice['footer_note']
            ?? \App\Helpers\InvoiceHelper::defaultFooterNote($company['name'] ?? $invoice['company']['name'] ?? null));
@endphp
<div data-print-block="signatures">
    @if ($invoice['show_signatures'] ?? true)
        <div class="row mt-3 pt-2 mb-0 invoice-a4-signatures">
            <div class="col-6 text-center">
                <div style="border-top: 1px solid #ced4da; width: 60%; margin: 16px auto 0; padding-top: 5px;">
                    <p class="text-uppercase text-muted fw-bold mb-0"
                        style="font-size: 10px; letter-spacing: 1px;">{{ \App\Helpers\InvoiceHelper::t('customer_signature') }}</p>
                </div>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px solid #ced4da; width: 60%; margin: 16px auto 0; padding-top: 5px;">
                    <p class="text-uppercase text-muted fw-bold mb-0"
                        style="font-size: 10px; letter-spacing: 1px;">{{ \App\Helpers\InvoiceHelper::t('company_signature') }}</p>
                </div>
            </div>
        </div>
    @endif
    <div class="text-center invoice-a4-footer mt-3">
        <p class="text-muted mb-1" style="font-size: 10px;">{{ $footerNote }}</p>
        @if (! empty($invoice['developer_credit']))
            <p class="text-uppercase text-secondary fw-bold mb-0" style="font-size: 9px; letter-spacing: 1.5px;">
                {{ $invoice['developer_credit'] }}
            </p>
        @endif
    </div>
</div>
