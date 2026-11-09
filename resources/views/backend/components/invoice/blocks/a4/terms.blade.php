@php
    $terms = $terms ?? ($invoice['terms'] ?? []);
@endphp
@if (count($terms) > 0)
    <div data-print-block="terms" class="rounded mb-3"
        style="border: 1px solid #e9ecef; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 p-3 mb-2">
            <h6 class="fw-bold mb-0 d-flex align-items-center" style="font-size: 13px; color: #000;">
                <i class="fas fa-check-circle me-2" style="color: #000 !important;"></i> {{ \App\Helpers\InvoiceHelper::t('terms') }}
            </h6>
            <span class="text-uppercase text-muted fw-bold border rounded px-2 py-1"
                style="font-size: 9px; letter-spacing: 1px; border-color: #dee2e6 !important;">{{ \App\Helpers\InvoiceHelper::t('terms_short') }}</span>
        </div>
        <div class="p-3 pt-1">
            <div class="rounded p-2 mt-1"
                style="background-color: #f1f3f5; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                @foreach ($terms as $term)
                    <div class="d-flex align-items-start mb-1">
                        <span class="me-2 text-muted" style="font-size: 9px; margin-top: 2px;">●</span>
                        <span class="text-dark" style="font-size: 10px; line-height: 1.3;">{{ $term }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
