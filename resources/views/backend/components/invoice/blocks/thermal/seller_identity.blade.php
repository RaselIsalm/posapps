@php
    $company = $company ?? ($invoice['company'] ?? []);
    $flags = $invoice['thermal_identity'] ?? \App\Helpers\InvoiceHelper::getThermalIdentityFlags();
    $address = trim((string) ($company['address'] ?? ''));
    $phone = trim((string) ($company['phone'] ?? ''));
    $telephone = trim((string) ($company['telephone'] ?? ''));
    $email = trim((string) ($company['email'] ?? ''));
    $website = trim((string) ($company['website'] ?? ''));
    $bin = trim((string) ($company['bin_number'] ?? ''));
    if ($website !== '') {
        $website = preg_replace('#^https?://#i', '', $website);
        $website = rtrim($website, '/');
    }
    $showPhone = $phone !== '' && ($flags['phone'] ?? true);
    $showTelephone = $telephone !== '' && ($flags['telephone'] ?? true);
    $showEmail = $email !== '' && ($flags['email'] ?? true);
    $showWebsite = $website !== '' && ($flags['website'] ?? true);
    $showBin = $bin !== '' && ($flags['bin'] ?? true);
    $contacts = [];
    if ($phone !== '') {
        $contacts[] = ['phone', $phone, $showPhone];
    }
    if ($telephone !== '') {
        $contacts[] = ['tel', $telephone, $showTelephone];
    }
    if ($email !== '') {
        $contacts[] = ['email', $email, $showEmail];
    }
    if ($website !== '') {
        $contacts[] = ['website', $website, $showWebsite];
    }
    $iconStyle = 'vertical-align: -2px; display: inline-block;';
@endphp
@if ($address !== '' || $bin !== '' || count($contacts))
<div data-print-block="seller_identity" style="text-align: center; margin: 0 0 8px 0; font-size: 10px; line-height: 1.45; color: #000;">
    @if ($address !== '')
        <div style="margin: 0 0 6px 0;">{{ $address }}</div>
    @endif

    @if (count($contacts))
        <div style="display: inline-block; text-align: left;">
            @foreach ($contacts as $contact)
                <div data-thermal-field="{{ $contact[0] === 'tel' ? 'telephone' : $contact[0] }}"
                    style="margin: 2px 0; white-space: nowrap; {{ $contact[2] ? '' : 'display:none;' }}">
                    <span style="display: inline-block; width: 18px; text-align: center; margin-right: 6px; vertical-align: middle;">
                        @if ($contact[0] === 'phone' || $contact[0] === 'tel')
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="{{ $iconStyle }}"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg>
                        @elseif ($contact[0] === 'email')
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="{{ $iconStyle }}"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        @else
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="{{ $iconStyle }}"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        @endif
                    </span><span style="vertical-align: middle;">{{ $contact[1] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($bin !== '')
        <div data-thermal-field="bin" style="margin-top: 5px; {{ $showBin ? '' : 'display:none;' }}">BIN: {{ $bin }}</div>
    @endif
</div>
@endif
