@php
    $company = $company ?? ($invoice['company'] ?? []);
    $headerColor = $headerColor ?? ($invoice['header_color'] ?? 'var(--primary-color, #308e87)');
    $displayName = $company['branch_name'] ?? $company['name'] ?? '';
    $showName = (bool) ($invoice['show_branch_name'] ?? $invoice['thermal_show_company_name'] ?? \App\Helpers\InvoiceHelper::getShowBranchName());
    if (empty($company['logo_url'])) {
        $showName = true;
    }
@endphp
<div data-print-block="header" class="row mb-2">
    <div class="col-7">
        @if (! empty($company['logo_url']))
            <div class="mb-2 a4-logo-wrap">
                <img src="{{ $company['logo_url'] }}" alt="Logo" class="a4-invoice-logo"
                    style="height: 48px; max-width: 220px; object-fit: contain; object-position: left;">
            </div>
        @else
            <div class="mb-2 a4-logo-wrap" style="display:none;">
                <img src="" alt="Logo" class="a4-invoice-logo"
                    style="height: 48px; max-width: 220px; object-fit: contain; object-position: left;">
            </div>
        @endif
        <h5 class="fw-bold mb-1 notranslate a4-branch-name" translate="no" lang="und"
            style="{{ $showName && $displayName !== '' ? '' : 'display:none;' }} color: #2c3e50;">{{ $displayName }}</h5>
        <div>
            @if (! empty($company['bin_number']))
                <p class="mb-0 text-muted" style="font-size: 10px;">BIN: {{ $company['bin_number'] }}</p>
            @endif
            @if (! empty($company['address']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ $company['address'] }}</p>
            @endif
            @if (! empty($company['phone']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ \App\Helpers\InvoiceHelper::t('phone') }}: {{ $company['phone'] }}</p>
            @endif
            @if (! empty($company['telephone']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ \App\Helpers\InvoiceHelper::t('tel') }}: {{ $company['telephone'] }}</p>
            @endif
            @if (! empty($company['website']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ $company['website'] }}</p>
            @endif
            @if (! empty($company['email']))
                <p class="mb-0 text-muted" style="font-size: 10px;">{{ $company['email'] }}</p>
            @endif
        </div>
    </div>
    <div class="col-5">
        <h1 class="text-end text-uppercase fw-bold mb-2"
            style="color: #e9ecef; font-size: 28px; letter-spacing: 2px;">
            {{ $invoice['document_label'] ?? \App\Helpers\InvoiceHelper::t('document_invoice') }}</h1>
        <div class="p-2 rounded float-end"
            style="background-color: #f8f9fa; border: 1px solid #e9ecef; width: 100%; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
            <table class="table table-sm table-borderless mb-0"
                style="font-size: 11px; color: #7f8c8d; font-weight: 600; width: 100%; background-color: transparent !important;">
                <tr style="background-color: transparent !important;">
                    <td class="text-uppercase text-end pe-3 py-1"
                        style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('invoice_hash') }}</td>
                    <td class="text-dark text-end fw-bold py-1"
                        style="background-color: transparent !important;">{{ $invoice['number'] ?? '' }}</td>
                </tr>
                <tr style="background-color: transparent !important;">
                    <td class="text-uppercase text-end pe-3 py-1"
                        style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('date') }}</td>
                    <td class="text-dark text-end fw-bold py-1"
                        style="background-color: transparent !important;">{{ $invoice['date_long'] ?? '' }}
                    </td>
                </tr>
                @if (! empty($invoice['status']))
                    <tr style="background-color: transparent !important;">
                        <td class="text-uppercase text-end pe-3 py-1"
                            style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('status') }}</td>
                        <td class="text-end fw-bold py-1"
                            style="color: #2c3e50; background-color: transparent !important;">
                            {{ \App\Helpers\InvoiceHelper::statusLabel($invoice['status']) }}</td>
                    </tr>
                @endif
                @if (! empty($invoice['served_by']))
                    <tr style="background-color: transparent !important;">
                        <td class="text-uppercase text-end pe-3 py-1"
                            style="background-color: transparent !important;">{{ \App\Helpers\InvoiceHelper::t('served_by') }}</td>
                        <td class="text-dark text-end fw-bold py-1"
                            style="background-color: transparent !important;">{{ $invoice['served_by'] }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</div>
