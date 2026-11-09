@php
    $invoice = $invoice ?? [];
    $company = $company ?? ($invoice['company'] ?? []);
    $thermalLogoUrl = $company['thermal_logo_url'] ?? null;
    $hasLogo = ! empty($thermalLogoUrl);
    $showName = (bool) ($invoice['show_branch_name'] ?? $invoice['thermal_show_company_name'] ?? \App\Helpers\InvoiceHelper::getShowBranchName());
    if (! $hasLogo) {
        $showName = true;
    }
    $logoHeight = (int) ($invoice['thermal_logo_height'] ?? \App\Helpers\InvoiceHelper::getThermalLogoHeight());
    $logoHeight = max(24, min(80, $logoHeight));
    $displayName = $company['branch_name'] ?? $company['name'] ?? '';
@endphp
<div data-print-block="header" style="text-align: center; margin-bottom: 8px;">
    <div class="thermal-logo-wrap" style="margin-bottom: 6px; {{ $hasLogo ? '' : 'display:none;' }}">
        <img src="{{ $thermalLogoUrl }}" alt="{{ $displayName }}"
            class="thermal-logo"
            style="display: inline-block; width: auto; height: auto; max-height: {{ $logoHeight }}px; max-width: 70%; object-fit: contain; -webkit-print-color-adjust: exact; print-color-adjust: exact;"
            onerror="var w=this.closest('.thermal-logo-wrap'); if(w){w.style.display='none';}">
    </div>
    <h2 class="thermal-company-name thermal-branch-name notranslate"
        translate="no"
        lang="und"
        style="{{ $showName ? '' : 'display:none;' }} margin: 0 0 4px 0; font-size: 15px; font-weight: 800; letter-spacing: 0.3px; color: #000;">
        {{ $displayName }}</h2>
</div>
