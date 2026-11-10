<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode Labels</title>
    @include('backend.layout.partials.theme-variables')
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f0f0;
            color: #000;
        }

        @page {
            size: {{ explode('x', $settings['paper_size'])[0] }}mm {{ explode('x', $settings['paper_size'])[1] }}mm;
            margin: 0;
        }

        .label-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px;
        }

        .sticker {
            background-color: white;
            width: {{ explode('x', $settings['paper_size'])[0] }}mm;
            height: {{ explode('x', $settings['paper_size'])[1] }}mm;
            box-sizing: border-box;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            text-align: center;
            border: 1px dotted #ccc;
            margin-bottom: 5px;
            padding: 1mm;
        }

        .sticker:last-child {
            page-break-after: auto;
            margin-bottom: 0;
        }

        @media print {
            body {
                background-color: white;
            }

            .label-container {
                display: block;
                padding: 0;
            }

            .sticker {
                border: none;
                margin: 0;
                width: {{ explode('x', $settings['paper_size'])[0] }}mm;
                height: {{ explode('x', $settings['paper_size'])[1] }}mm;
            }

            .no-print {
                display: none !important;
            }
        }

        .company-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1;
        }

        .product-name {
            font-size: 8px;
            margin-bottom: 2px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 95%;
        }

        .barcode-img {
            max-width: 90%;
            height: 8mm;
            margin: 1px 0;
        }

        .product-code {
            font-size: 7px;
            letter-spacing: 1px;
            margin-top: 1px;
            line-height: 1;
        }

        .price {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2px;
            line-height: 1;
        }

        .print-btn-container {
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="print-btn-container no-print">
        <button class="btn" onclick="window.print()">🖨️ Print Labels</button>
        <button class="btn" style="background:#6c757d; margin-left:5px;" onclick="window.close()">Close</button>
    </div>

    <div class="label-container">
        @foreach ($barcodesToPrint as $label)
            <div class="sticker">

                @if ($settings['show_company'])
                    <div class="company-name">{{ $settings['company_name'] }}</div>
                @endif

                @if ($settings['show_product_name'])
                    <div class="product-name">{{ $label['product_name'] }}</div>
                @endif

                <img class="barcode-img" src="data:image/png;base64,{{ $label['barcode'] }}" alt="barcode">

                @if ($settings['show_product_code'])
                    <div class="product-code">{{ $label['product_code'] }}</div>
                @endif

                @if ($settings['show_price'])
                    <div class="price">Price: {{ \App\Helpers\CurrencyHelper::format($label['price'], 2) }}</div>
                @endif

            </div>
        @endforeach
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>

</body>

</html>
