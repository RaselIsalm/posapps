<div data-print-block="banner" class="thermal-banner"
    style="background-color: #000 !important; color: #fff !important; text-align: center; padding: 4px 0; margin: 10px 0; font-weight: bold; font-size: 12px; letter-spacing: 1px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
    {{ $invoice['thermal_title'] ?? \App\Helpers\InvoiceHelper::t('document_receipt') }}
</div>
