{{--
  Shared thermal receipt body.
  Expects $invoice array (InvoiceHelper). Optional: $company, $billTo, $items, $terms, $totalDiscount, $printBlocks.
--}}
@php
    $invoice = $invoice ?? [];
    $company = $company ?? ($invoice['company'] ?? []);
    $billTo = $billTo ?? ($invoice['bill_to'] ?? []);
    $items = $items ?? ($invoice['items'] ?? []);
    $terms = $terms ?? ($invoice['terms'] ?? []);
    $totalDiscount = $totalDiscount ?? \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $thankYou = $invoice['thermal_thank_you'] ?? \App\Helpers\InvoiceHelper::t('thank_you');
    $receivedDisplay = max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = (float) ($invoice['change_amount'] ?? 0);
    $printBlocks = $printBlocks ?? \App\Helpers\InvoiceHelper::enabledPrintBlocks('thermal');
@endphp

<div class="thermal-receipt-body"
    style="width: 100%; max-width: 100%; margin: 0 auto; padding: 0; box-sizing: border-box; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; color: #000; background: #fff; line-height: 1.45; text-align: left;">

    @foreach ($printBlocks as $block)
        @include('backend.components.invoice.blocks.thermal.'.$block['id'])
    @endforeach
</div>
