@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Print Settings
@endsection

@push('css')
    <style>
        .print-block-list { list-style: none; padding: 0; margin: 0; }
        .print-block-item {
            display: flex; align-items: center; gap: 10px;
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
            padding: 10px 12px; margin-bottom: 8px; cursor: grab;
        }
        .print-block-item .drag-handle,
        .print-block-item .fa-grip-vertical {
            color: #4b5563 !important;
            font-size: 16px;
            cursor: grab;
        }
        .print-block-item.sortable-ghost { opacity: .45; }
        #printTabs .nav-link {
            color: var(--primary-color, #308e87) !important;
            background: transparent !important;
            border: none;
            font-weight: 600;
        }
        #printTabs .nav-link.active,
        #printTabs .nav-link.active:hover,
        #printTabs .nav-link.active:focus {
            background: var(--primary-color, #308e87) !important;
            color: #fff !important;
        }
        .print-preview-wrap {
            background: #525659;
            border-radius: 8px;
            padding: 24px 16px 40px;
            overflow: auto;
        }
        .invoice-preview-mode #a4_print_invoice_content { display: block !important; }
        .invoice-preview-mode .d-print-none { display: none !important; }
        .thermal-preview-frame {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 2mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,.5);
            overflow: visible;
        }
        .a4-preview-scaler {
            width: 100%;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }
        .a4-preview-frame {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            background: #fff;
            padding: 10mm 12mm;
            box-sizing: border-box;
            box-shadow: 0 0 14px rgba(0,0,0,.45);
            transform-origin: top center;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .a4-preview-frame .preview-block[data-id="signatures"] {
            margin-top: auto;
        }
        .preview-block[data-enabled="0"] { display: none !important; }
    </style>
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')Print Settings @endslot
    @slot('subtitle')Reorder invoice and POS receipt sections. Preview updates instantly. @endslot
    <li class="breadcrumb-item">Settings</li>
    <li class="breadcrumb-item active">Print Settings</li>
@endcomponent

@php
    $company = $invoice['company'] ?? [];
    $from = $invoice['from'] ?? [];
    $billTo = $invoice['bill_to'] ?? [];
    $items = $invoice['items'] ?? [];
    $discounts = $invoice['discounts'] ?? [];
    $charges = $invoice['charges'] ?? [];
    $terms = $invoice['terms'] ?? [];
    $headerColor = $invoice['header_color'] ?? 'var(--primary-color, #308e87)';
    $totalDiscount = \App\Helpers\InvoiceHelper::totalDiscount($invoice);
    $totalCharges = \App\Helpers\InvoiceHelper::totalCharges($invoice);
    $footerNote = $invoice['footer_note'] ?? \App\Helpers\InvoiceHelper::defaultFooterNote();
    $receivedDisplay = max((float) ($invoice['received_amount'] ?? 0), (float) ($invoice['paid_amount'] ?? 0));
    $changeDisplay = (float) ($invoice['change_amount'] ?? 0);
    $thankYou = $invoice['thermal_thank_you'] ?? \App\Helpers\InvoiceHelper::t('thank_you');
@endphp

<div class="container-fluid">
    <form action="{{ route('settings.printUpdate') }}" method="POST" id="printLayoutForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="print_show_branch_name" value="0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-pills" id="printTabs" role="tablist">
                <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-a4">A4 Invoice</button></li>
                <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-thermal">POS / Thermal</button></li>
            </ul>
            <button type="submit" class="btn btn-primary">Save layout</button>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-a4">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card mb-3"><div class="card-body">
                            <label class="fw-bold mb-2">A4 invoice logo</label>
                            <p class="text-muted small mb-2">Used only on A4 invoices. Thermal receipts use the POS logo.</p>
                            <input type="file" class="form-control form-control-sm mb-2" id="a4_logo" name="a4_logo" accept="image/*">
                            <input type="hidden" name="remove_a4_logo" id="remove_a4_logo" value="0">
                            @php
                                $a4LogoFile = is_string($a4Logo ?? null) ? $a4Logo : ($invoice['company']['a4_logo'] ?? null);
                                $a4LogoFile = is_string($a4LogoFile) ? $a4LogoFile : null;
                                $a4LogoUrl = \App\Helpers\ImageHelper::publicUrl($a4LogoFile, 'settings');
                            @endphp
                            <div id="a4_logo_saved" class="text-center mb-2" @if(empty($a4LogoUrl)) style="display:none;" @endif>
                                <img id="a4_logo_saved_img"
                                    src="{{ $a4LogoUrl ?? '' }}"
                                    alt="A4 logo"
                                    style="max-height: 56px; max-width: 100%; object-fit: contain;"
                                    onerror="this.closest('#a4_logo_saved')?.style && (this.closest('#a4_logo_saved').style.display='none')">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeA4LogoBtn">Remove logo</button>
                                </div>
                            </div>
                            <div id="a4_logo_preview_wrap" class="text-center mb-2" style="display:none;">
                                <img id="a4_logo_preview" src="" alt="Preview" style="max-height: 56px; max-width: 100%;">
                            </div>
                            <hr>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input print-show-branch-name" type="checkbox" role="switch"
                                    id="a4ShowBranchName" name="print_show_branch_name" value="1"
                                    {{ ! empty($showBranchName) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="a4ShowBranchName">Show branch name</label>
                            </div>
                            <p class="text-muted small mb-0 mt-1">Company name is never printed. Turn off to hide the branch name under the A4 logo.</p>
                        </div></div>
                        <div class="card"><div class="card-body">
                            <p class="text-muted small mb-3">Drag to change order. Toggle to show or hide a section.</p>
                            <ul class="print-block-list" id="a4List">
                                @foreach ($a4Blocks as $block)
                                    <li class="print-block-item" data-id="{{ $block['id'] }}">
                                        <i class="fas fa-grip-vertical drag-handle"></i>
                                        <span class="flex-grow-1">{{ $block['label'] }}</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input block-enabled" type="checkbox" {{ $block['enabled'] ? 'checked' : '' }}>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div></div>
                    </div>
                    <div class="col-lg-8">
                        <div class="print-preview-wrap invoice-preview-mode">
                            <div class="a4-preview-scaler">
                                <div class="a4-preview-frame" id="a4Preview">
                                    @foreach ($a4Blocks as $block)
                                        <div class="preview-block" data-id="{{ $block['id'] }}" data-enabled="{{ $block['enabled'] ? '1' : '0' }}">
                                            @include('backend.components.invoice.blocks.a4.'.$block['id'])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-thermal">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card mb-3"><div class="card-body">
                            <label class="fw-bold mb-2">POS / Thermal logo</label>
                            <p class="text-muted small mb-2">This logo is used only on thermal receipts. A4 invoices still use the General Settings logo.</p>
                            <input type="file" class="form-control form-control-sm mb-2" id="thermal_logo" name="thermal_logo" accept="image/*">
                            <input type="hidden" name="remove_thermal_logo" id="remove_thermal_logo" value="0">
                            @php
                                $thermalLogoFile = is_string($thermalLogo ?? null) ? $thermalLogo : ($invoice['company']['thermal_logo'] ?? null);
                                $thermalLogoFile = is_string($thermalLogoFile) ? $thermalLogoFile : null;
                                $thermalLogoUrl = \App\Helpers\ImageHelper::publicUrl($thermalLogoFile, 'settings');
                            @endphp
                            <div id="thermal_logo_saved" class="text-center mb-2" @if(empty($thermalLogoUrl)) style="display:none;" @endif>
                                <img id="thermal_logo_saved_img" class="thermal-logo"
                                    src="{{ $thermalLogoUrl ?? '' }}"
                                    alt="Thermal logo"
                                    style="max-height: {{ (int) ($thermalLogoHeight ?? 40) }}px; max-width: 100%; object-fit: contain;"
                                    onerror="this.closest('#thermal_logo_saved')?.style && (this.closest('#thermal_logo_saved').style.display='none')">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeThermalLogoBtn">Remove logo</button>
                                </div>
                            </div>
                            <div id="thermal_logo_preview_wrap" class="text-center mb-2" style="display:none;">
                                <img id="thermal_logo_preview" class="thermal-logo" src="" alt="Preview"
                                    style="max-height: {{ (int) ($thermalLogoHeight ?? 40) }}px; max-width: 100%; object-fit: contain;"
                                    onerror="this.parentElement && (this.parentElement.style.display='none')">
                            </div>
                            <hr>
                            <label class="fw-bold mb-2" for="thermalLogoHeight">Thermal logo size</label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted">Small</span>
                                <input type="range" class="form-range flex-grow-1" min="24" max="80" step="2"
                                    id="thermalLogoHeight" name="thermal_logo_height"
                                    value="{{ (int) ($thermalLogoHeight ?? 40) }}">
                                <span class="small text-muted">Large</span>
                            </div>
                            <div class="text-center small text-muted mt-1">
                                Height: <strong id="thermalLogoHeightLabel">{{ (int) ($thermalLogoHeight ?? 40) }}px</strong>
                            </div>
                            <hr>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input print-show-branch-name" type="checkbox" role="switch"
                                    id="thermalShowBranchName" value="1"
                                    {{ ! empty($showBranchName) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="thermalShowBranchName">Show branch name</label>
                            </div>
                            <p class="text-muted small mb-0 mt-1">Company name is never printed. Turn off to hide the branch name under the thermal logo.</p>
                            <hr>
                            <label class="fw-bold mb-2">Show on thermal receipt</label>
                            @php $thermalIdentity = $thermalIdentity ?? \App\Helpers\InvoiceHelper::getThermalIdentityFlags(); @endphp
                            @foreach (\App\Helpers\InvoiceHelper::thermalIdentityToggles() as $field => $label)
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input thermal-identity-toggle" type="checkbox" role="switch"
                                        id="thermal_show_{{ $field }}" name="thermal_show_{{ $field }}" value="1"
                                        data-field="{{ $field }}"
                                        {{ ! empty($thermalIdentity[$field]) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="thermal_show_{{ $field }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div></div>
                        <div class="card"><div class="card-body">
                            <p class="text-muted small mb-3">Same blocks used on Quick Sale and Sale List thermal receipts.</p>
                            <ul class="print-block-list" id="thermalList">
                                @foreach ($thermalBlocks as $block)
                                    <li class="print-block-item" data-id="{{ $block['id'] }}">
                                        <i class="fas fa-grip-vertical drag-handle"></i>
                                        <span class="flex-grow-1">{{ $block['label'] }}</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input block-enabled" type="checkbox" {{ $block['enabled'] ? 'checked' : '' }}>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div></div>
                    </div>
                    <div class="col-lg-8">
                        <div class="print-preview-wrap">
                            <div class="thermal-preview-frame">
                                <div class="thermal-receipt-body" id="thermalPreview"
                                    style="width: 100%; max-width: 100%; margin: 0; padding: 0 4mm; box-sizing: border-box; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; background: #fff; line-height: 1.5;">
                                    @foreach ($thermalBlocks as $block)
                                        <div class="preview-block" data-id="{{ $block['id'] }}" data-enabled="{{ $block['enabled'] ? '1' : '0' }}">
                                            @include('backend.components.invoice.blocks.thermal.'.$block['id'])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    $('#settings').addClass('active');
    $('#settings-submenu').addClass('d-block');

    function scaleA4Preview() {
        const paper = document.getElementById('a4Preview');
        const scaler = paper ? paper.closest('.a4-preview-scaler') : null;
        if (!paper || !scaler) return;
        const paperWidth = paper.offsetWidth || (210 * 3.7795);
        const paperHeight = paper.offsetHeight || (297 * 3.7795);
        const available = scaler.clientWidth || scaler.parentElement.clientWidth;
        const scale = Math.min(1, available / paperWidth);
        paper.style.transform = 'scale(' + scale + ')';
        scaler.style.height = (paperHeight * scale) + 'px';
    }

    function syncPreview(listId, previewId) {
        const list = document.getElementById(listId);
        const preview = document.getElementById(previewId);
        if (!list || !preview) return;
        list.querySelectorAll('.print-block-item').forEach(item => {
            const id = item.dataset.id;
            const enabled = item.querySelector('.block-enabled').checked;
            const block = preview.querySelector('.preview-block[data-id="' + id + '"]');
            if (!block) return;
            block.dataset.enabled = enabled ? '1' : '0';
            preview.appendChild(block);
        });
        if (previewId === 'a4Preview') {
            scaleA4Preview();
        }
    }

    function bindList(listId, previewId) {
        const el = document.getElementById(listId);
        new Sortable(el, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: () => syncPreview(listId, previewId),
        });
        el.querySelectorAll('.block-enabled').forEach(cb => {
            cb.addEventListener('change', () => syncPreview(listId, previewId));
        });
        syncPreview(listId, previewId);
    }

    bindList('a4List', 'a4Preview');
    bindList('thermalList', 'thermalPreview');
    scaleA4Preview();
    window.addEventListener('resize', scaleA4Preview);
    document.querySelectorAll('#printTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', scaleA4Preview);
    });

    const logoSlider = document.getElementById('thermalLogoHeight');
    const logoLabel = document.getElementById('thermalLogoHeightLabel');
    function applyThermalLogoSize(px) {
        if (logoLabel) logoLabel.textContent = px + 'px';
        document.querySelectorAll('.thermal-logo').forEach(img => {
            img.style.maxHeight = px + 'px';
        });
    }
    if (logoSlider) {
        applyThermalLogoSize(logoSlider.value);
        logoSlider.addEventListener('input', function () {
            applyThermalLogoSize(this.value);
        });
    }

    const thermalLogoInput = document.getElementById('thermal_logo');
    const removeThermalLogo = document.getElementById('remove_thermal_logo');
    const showNameToggles = document.querySelectorAll('.print-show-branch-name');
    function syncBranchNamePreview() {
        const checked = Array.from(showNameToggles).some(el => el.checked);
        showNameToggles.forEach(el => { el.checked = checked; });
        const thermalName = document.querySelector('#thermalPreview .thermal-company-name');
        const wrap = document.querySelector('#thermalPreview .thermal-logo-wrap');
        const hasLogo = wrap && wrap.style.display !== 'none' && wrap.querySelector('img') && wrap.querySelector('img').getAttribute('src');
        if (thermalName) {
            thermalName.style.display = (!hasLogo || checked) ? '' : 'none';
        }
        const a4Name = document.querySelector('#a4Preview .a4-branch-name');
        const a4Wrap = document.querySelector('#a4Preview .a4-logo-wrap');
        const a4HasLogo = a4Wrap && a4Wrap.style.display !== 'none' && a4Wrap.querySelector('img') && a4Wrap.querySelector('img').getAttribute('src');
        if (a4Name) {
            a4Name.style.display = (!a4HasLogo || checked) ? '' : 'none';
        }
    }
    function setThermalPreviewLogo(url) {
        const wrap = document.querySelector('#thermalPreview .thermal-logo-wrap');
        const img = document.querySelector('#thermalPreview img.thermal-logo');
        if (url) {
            if (img) img.src = url;
            if (wrap) wrap.style.display = '';
        } else {
            if (wrap) wrap.style.display = 'none';
        }
        syncBranchNamePreview();
    }
    function setA4PreviewLogo(url) {
        const wrap = document.querySelector('#a4Preview .a4-logo-wrap');
        const img = document.querySelector('#a4Preview img.a4-invoice-logo');
        if (url) {
            if (img) img.src = url;
            if (wrap) wrap.style.display = '';
        } else {
            if (wrap) wrap.style.display = 'none';
        }
        syncBranchNamePreview();
    }
    showNameToggles.forEach(el => el.addEventListener('change', function () {
        showNameToggles.forEach(other => { other.checked = this.checked; });
        syncBranchNamePreview();
    }));
    document.querySelectorAll('.thermal-identity-toggle').forEach(cb => {
        cb.addEventListener('change', function () {
            document.querySelectorAll('#thermalPreview [data-thermal-field="' + this.dataset.field + '"]').forEach(el => {
                el.style.display = this.checked ? '' : 'none';
            });
        });
    });
    if (thermalLogoInput) {
        thermalLogoInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const saved = document.getElementById('thermal_logo_saved');
            const previewWrap = document.getElementById('thermal_logo_preview_wrap');
            const previewImg = document.getElementById('thermal_logo_preview');
            if (!file) return;
            const url = URL.createObjectURL(file);
            if (removeThermalLogo) removeThermalLogo.value = '0';
            if (saved) saved.style.display = 'none';
            if (previewWrap) previewWrap.style.display = '';
            if (previewImg) previewImg.src = url;
            setThermalPreviewLogo(url);
            applyThermalLogoSize(logoSlider ? logoSlider.value : 40);
        });
    }
    const removeBtn = document.getElementById('removeThermalLogoBtn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (removeThermalLogo) removeThermalLogo.value = '1';
            const saved = document.getElementById('thermal_logo_saved');
            const previewWrap = document.getElementById('thermal_logo_preview_wrap');
            if (saved) saved.style.display = 'none';
            if (previewWrap) previewWrap.style.display = 'none';
            if (thermalLogoInput) thermalLogoInput.value = '';
            setThermalPreviewLogo('');
        });
    }

    const a4LogoInput = document.getElementById('a4_logo');
    const removeA4Logo = document.getElementById('remove_a4_logo');
    if (a4LogoInput) {
        a4LogoInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const saved = document.getElementById('a4_logo_saved');
            const previewWrap = document.getElementById('a4_logo_preview_wrap');
            const previewImg = document.getElementById('a4_logo_preview');
            if (!file) return;
            const url = URL.createObjectURL(file);
            if (removeA4Logo) removeA4Logo.value = '0';
            if (saved) saved.style.display = 'none';
            if (previewWrap) previewWrap.style.display = '';
            if (previewImg) previewImg.src = url;
            setA4PreviewLogo(url);
        });
    }
    const removeA4Btn = document.getElementById('removeA4LogoBtn');
    if (removeA4Btn) {
        removeA4Btn.addEventListener('click', function () {
            if (removeA4Logo) removeA4Logo.value = '1';
            const saved = document.getElementById('a4_logo_saved');
            const previewWrap = document.getElementById('a4_logo_preview_wrap');
            if (saved) saved.style.display = 'none';
            if (previewWrap) previewWrap.style.display = 'none';
            if (a4LogoInput) a4LogoInput.value = '';
            setA4PreviewLogo('');
        });
    }

    document.getElementById('printLayoutForm').addEventListener('submit', function () {
        document.querySelectorAll('input[data-generated="1"]').forEach(n => n.remove());
        [['a4List', 'print_layout_a4'], ['thermalList', 'print_layout_thermal']].forEach(([listId, name]) => {
            document.querySelectorAll('#' + listId + ' .print-block-item').forEach((item, i) => {
                const id = item.dataset.id;
                const enabled = item.querySelector('.block-enabled').checked ? '1' : '0';
                [['id', id], ['enabled', enabled]].forEach(([key, val]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name + '[' + i + '][' + key + ']';
                    input.value = val;
                    input.dataset.generated = '1';
                    this.appendChild(input);
                });
            });
        });
    });
</script>
@endpush
