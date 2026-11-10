{{--
  Standalone A4 print page (auto-print).
  Pass $invoice from InvoiceHelper::* with optional overrides.
--}}
@php
    $invoice = $invoice ?? [];
    $company = $invoice['company'] ?? [];
    $billTo = $invoice['bill_to'] ?? [];
    $items = $invoice['items'] ?? [];
    $discounts = $invoice['discounts'] ?? [];
    $charges = $invoice['charges'] ?? [];
    $terms = $invoice['terms'] ?? [];
    $headerColor = $invoice['header_color'] ?? 'var(--primary-color, #308e87)';
    $totalDiscount = \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $totalCharges = \App\Helpers\InvoiceHelper::totalCharges($invoice);
    $footerNote = $invoice['footer_note']
        ?? \App\Helpers\InvoiceHelper::defaultFooterNote($company['name'] ?? null, $company['phone'] ?? null);
    $pageTitle = ' ';
    $receivedDisplay = max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = (float) ($invoice['change_amount'] ?? 0);
    $from = $invoice['from'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @page {
            @top-left { content: none; }
            @top-center { content: none; }
            @top-right { content: none; }
            @bottom-left { content: none; }
            @bottom-center { content: none; }
            @bottom-right { content: none; }
        }
        html, body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        #a4_print_invoice_content { display: block !important; }
        .d-print-none, .btn-close { display: none !important; }
        .invoice-a4-body {
            display: flex !important;
            flex-direction: column !important;
            min-height: 270mm !important;
            box-sizing: border-box !important;
            padding: 10mm !important;
        }
        .invoice-a4-main { flex: 1 1 auto !important; }
        .invoice-a4-footer {
            flex-shrink: 0 !important;
            margin-top: auto !important;
            padding-top: 10px !important;
        }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; }
            .invoice-a4-body { padding: 10mm !important; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function () { window.close(); }, 800);">
    @include('backend.components.invoice.partials.a4', compact(
        'invoice', 'company', 'billTo', 'from', 'items', 'discounts', 'charges', 'terms',
        'headerColor', 'totalDiscount', 'totalCharges', 'footerNote',
        'receivedDisplay', 'changeDisplay'
    ))
</body>
</html>
