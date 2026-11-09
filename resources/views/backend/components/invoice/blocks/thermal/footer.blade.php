@php
    $terms = $terms ?? ($invoice['terms'] ?? []);
    $thankYou = $thankYou ?? ($invoice['thermal_thank_you'] ?? \App\Helpers\InvoiceHelper::t('thank_you'));
@endphp
<div data-print-block="footer">
    @if (count($terms) > 0 && ($invoice['show_thermal_terms'] ?? true))
        <div class="thermal-banner"
            style="background-color: #000 !important; color: #fff !important; text-align: center; padding: 4px 0; margin: 10px 0 5px 0; font-weight: bold; font-size: 10px; letter-spacing: 1px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
            {{ \App\Helpers\InvoiceHelper::t('terms') }}
        </div>
        <div style="font-size: 9px; text-align: left; padding: 0 5px; margin-bottom: 10px;">
            <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                @foreach ($terms as $term)
                    <li style="margin-bottom: 2px;">{{ $term }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="text-align: center; margin-top: 15px;">
        <p style="margin: 0; font-weight: 800; font-size: 13px; margin-bottom: 3px;">{{ $thankYou }}</p>
    </div>

    <div style="border-bottom: 1.5px solid #000; margin: 10px 0;"></div>

    @if (! empty($invoice['developer_credit']))
        <div style="text-align: center; font-size: 9px;">
            <p style="margin: 2px 0; font-weight: bold;">{{ $invoice['developer_credit'] }}</p>
        </div>
    @endif
</div>
