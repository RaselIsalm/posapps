{{--
  Standalone thermal page — CSS/markup match Sale List printThermalHtml() exactly.
--}}
@php
    $invoice = $invoice ?? [];
    $thermalWidth = \App\Helpers\InvoiceHelper::normalizeThermalSize(
        $thermal_width ?? ($invoice['thermal_width'] ?? null)
    );
    $paperMm = (float) $thermalWidth;
    $contentMm = max($paperMm - 8, (int) round($paperMm * 0.9));
    $contentWidth = $contentMm.'mm';
    $pageTitle = ' ';
@endphp
<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <style>
        @media print {
            @page { margin: 0; size: {{ $thermalWidth }} auto; }
            @page {
                @top-left { content: none; }
                @top-center { content: none; }
                @top-right { content: none; }
                @bottom-left { content: none; }
                @bottom-center { content: none; }
                @bottom-right { content: none; }
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: hidden !important;
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .thermal-center-wrap {
                width: 100% !important;
                border-collapse: collapse !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .thermal-center-wrap > tbody > tr > td {
                padding: 0 !important;
                margin: 0 !important;
                text-align: center !important;
                vertical-align: top !important;
            }
            #thermal_container {
                display: block !important;
                width: {{ $contentWidth }} !important;
                max-width: 92% !important;
                margin: 0 auto !important;
                padding: 2mm 0 !important;
                box-sizing: border-box !important;
                box-shadow: none !important;
                border: none !important;
                background: #fff !important;
                font-weight: 600 !important;
                font-size: 12px !important;
                color: #000 !important;
                text-align: left !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .thermal-receipt-body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                color: #000 !important;
                line-height: 1.45 !important;
                text-align: left !important;
            }
            .text-muted, .text-secondary, small, .small {
                color: #000 !important;
                opacity: 1 !important;
                font-weight: 600 !important;
            }
            strong, b, .fw-bold, .font-weight-bold {
                font-weight: 800 !important;
                color: #000 !important;
            }
            .thermal-banner {
                background: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .thermal-banner * {
                color: #fff !important;
            }
            .thermal-logo {
                display: inline-block !important;
                width: auto !important;
                height: auto !important;
                max-width: 70% !important;
                object-fit: contain !important;
                filter: none !important;
                -webkit-filter: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: auto;
            min-height: 0;
            background-color: #525659;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            padding: 16px 0;
        }
        .thermal-center-wrap {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        .thermal-center-wrap > tbody > tr > td {
            padding: 0;
            text-align: center;
            vertical-align: top;
        }
        #thermal_container {
            display: block;
            width: {{ $contentWidth }};
            max-width: 92%;
            margin: 0 auto;
            padding: 2mm 0;
            box-sizing: border-box;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            border: none;
            text-align: left;
            color: #000;
            font-weight: 600;
            font-size: 12px;
        }
        .thermal-receipt-body {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 12px;
            font-weight: 600;
            color: #000;
            text-align: left;
        }
        .text-muted, .text-secondary {
            color: #000 !important;
            opacity: 1;
        }
        .thermal-banner {
            background: #000 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .thermal-banner * {
            color: #fff !important;
        }
        .thermal-logo {
            display: inline-block;
            width: auto;
            height: auto;
            max-width: 70%;
            object-fit: contain;
            filter: none;
            -webkit-filter: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>
    <table class="thermal-center-wrap" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" valign="top">
                <div id="thermal_container">
                    @include('backend.components.invoice.partials.thermal_body', ['invoice' => $invoice])
                </div>
            </td>
        </tr>
    </table>
    <script>
        (function () {
            var printed = false;
            var runPrint = function () {
                if (printed) return;
                printed = true;
                window.onafterprint = function () {
                    setTimeout(function () { window.close(); }, 300);
                };
                setTimeout(function () { window.close(); }, 15000);
                window.print();
            };
            var images = Array.prototype.slice.call(document.images || []);
            if (!images.length) {
                setTimeout(runPrint, 300);
                return;
            }
            var pending = images.length;
            var settle = function () {
                pending -= 1;
                if (pending <= 0) setTimeout(runPrint, 300);
            };
            images.forEach(function (img) {
                if (img.complete) settle();
                else {
                    img.addEventListener('load', settle);
                    img.addEventListener('error', settle);
                }
            });
            setTimeout(runPrint, 2000);
        })();
    </script>
</body>
</html>
