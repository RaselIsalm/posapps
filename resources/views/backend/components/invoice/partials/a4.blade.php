{{-- A4 print layout (hidden; used by printInvoiceSection) --}}
@php
    $printBlocks = $printBlocks ?? \App\Helpers\InvoiceHelper::enabledPrintBlocks('a4');
    $from = $from ?? ($invoice['from'] ?? []);
@endphp
<div id="a4_print_invoice_content"
    style="display: none; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #fff; position: relative; color: #333;">

    <div class="d-print-none d-flex justify-content-between align-items-center p-3 text-white rounded-top"
        style="background-color: {{ $headerColor }};">
        <h5 class="mb-0 fw-bold">{{ $invoice['modal_title'] ?? \App\Helpers\InvoiceHelper::t('invoice') }}</h5>
        <button type="button" class="btn-close btn-close-white" onclick="forceCloseInvoiceModal()"></button>
    </div>

    <div class="invoice-a4-body">
        <div class="invoice-a4-main">
            @foreach ($printBlocks as $block)
                @include('backend.components.invoice.blocks.a4.'.$block['id'])
            @endforeach
        </div>
    </div>
</div>
