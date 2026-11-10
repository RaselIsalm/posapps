@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Color Settings
@endsection

@push('css')
    <style>
        .color-field-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .color-field-row input[type="color"] {
            width: 48px;
            height: 38px;
            padding: 2px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            background: #fff;
        }
        .color-preview-card {
            border: 1px solid #ebebeb;
            border-radius: 12px;
            padding: 20px;
            background: #f9fafb;
        }
        .color-preview-toast {
            border-left: 4px solid var(--preview-toast, #308e87);
            background: #fff;
            border-radius: 6px;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-top: 12px;
        }
        .preview-btn {
            background: var(--preview-primary, #308e87) !important;
            border-color: var(--preview-primary, #308e87) !important;
            color: #fff !important;
        }
        .preview-btn:hover {
            background: var(--preview-hover, #256e69) !important;
            border-color: var(--preview-hover, #256e69) !important;
            color: #fff !important;
        }
        .preview-link {
            color: var(--preview-link, #308e87) !important;
            font-weight: 600;
        }
        .preview-swatch {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')Color Settings @endslot
    @slot('subtitle')Change primary, hover, link and toast colors for the whole application from one place. @endslot
<li class="breadcrumb-item">Settings</li>
<li class="breadcrumb-item active">Color Settings</li>
@endcomponent

<div class="container-fluid default-dashboard">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Theme Colors</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.colorsUpdate') }}" method="POST" id="colorSettingsForm">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="primary_color" class="form-label fw-semibold">Primary Color</label>
                                <div class="color-field-row">
                                    <input type="color" id="primary_color_picker" value="{{ old('primary_color', $colors['primary_color']) }}">
                                    <input type="text" class="form-control" id="primary_color" name="primary_color"
                                        value="{{ old('primary_color', $colors['primary_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required>
                                </div>
                                <small class="text-muted">Default: #308e87 — buttons, accents, active states</small>
                                @error('primary_color')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="primary_hover_color" class="form-label fw-semibold">Hover Color</label>
                                <div class="color-field-row">
                                    <input type="color" id="primary_hover_color_picker" value="{{ old('primary_hover_color', $colors['primary_hover_color']) }}">
                                    <input type="text" class="form-control" id="primary_hover_color" name="primary_hover_color"
                                        value="{{ old('primary_hover_color', $colors['primary_hover_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required>
                                </div>
                                <small class="text-muted">Default: #256e69 — button/link hover</small>
                                @error('primary_hover_color')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="link_color" class="form-label fw-semibold">Link Color</label>
                                <div class="color-field-row">
                                    <input type="color" id="link_color_picker" value="{{ old('link_color', $colors['link_color']) }}">
                                    <input type="text" class="form-control" id="link_color" name="link_color"
                                        value="{{ old('link_color', $colors['link_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required>
                                </div>
                                <small class="text-muted">Default: same as primary</small>
                                @error('link_color')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="toast_success_color" class="form-label fw-semibold">Toast / Success Color</label>
                                <div class="color-field-row">
                                    <input type="color" id="toast_success_color_picker" value="{{ old('toast_success_color', $colors['toast_success_color']) }}">
                                    <input type="text" class="form-control" id="toast_success_color" name="toast_success_color"
                                        value="{{ old('toast_success_color', $colors['toast_success_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required>
                                </div>
                                <small class="text-muted">Success notifications &amp; confirm buttons</small>
                                @error('toast_success_color')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="primary_light_color" class="form-label fw-semibold">Light Background</label>
                                <div class="color-field-row">
                                    <input type="color" id="primary_light_color_picker" value="{{ old('primary_light_color', $colors['primary_light_color']) }}">
                                    <input type="text" class="form-control" id="primary_light_color" name="primary_light_color"
                                        value="{{ old('primary_light_color', $colors['primary_light_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required>
                                </div>
                                <small class="text-muted">Default: #eaf4f3 — soft hover backgrounds</small>
                                @error('primary_light_color')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Colors
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="suggestHoverBtn">
                                Auto Hover from Primary
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('settings.colorsReset') }}" method="POST" class="mt-3" id="colorsResetForm">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-undo me-1"></i> Reset to Default
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Live Preview</h5>
                </div>
                <div class="card-body">
                    <div class="color-preview-card" id="colorPreviewCard">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="preview-swatch" id="previewSwatch"></div>
                            <div>
                                <div class="fw-bold">Primary Theme</div>
                                <small class="text-muted" id="previewHexLabel">#308e87</small>
                            </div>
                        </div>

                        <button type="button" class="btn preview-btn mb-2">Primary Button</button>
                        <div class="mb-2">
                            <a href="javascript:void(0)" class="preview-link">Sample link hover</a>
                        </div>
                        <div class="color-preview-toast">
                            <strong>Success!</strong> Settings saved successfully.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    (function () {
        const fields = [
            'primary_color',
            'primary_hover_color',
            'link_color',
            'toast_success_color',
            'primary_light_color'
        ];

        function syncPair(name) {
            const text = document.getElementById(name);
            const picker = document.getElementById(name + '_picker');
            if (!text || !picker) return;

            picker.addEventListener('input', function () {
                text.value = picker.value.toLowerCase();
                updatePreview();
            });

            text.addEventListener('input', function () {
                let val = text.value.trim();
                if (!val.startsWith('#')) val = '#' + val;
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                    picker.value = val;
                    text.value = val.toLowerCase();
                }
                updatePreview();
            });
        }

        function updatePreview() {
            const primary = document.getElementById('primary_color').value || '#308e87';
            const hover = document.getElementById('primary_hover_color').value || '#256e69';
            const link = document.getElementById('link_color').value || primary;
            const toast = document.getElementById('toast_success_color').value || primary;
            const card = document.getElementById('colorPreviewCard');

            card.style.setProperty('--preview-primary', primary);
            card.style.setProperty('--preview-hover', hover);
            card.style.setProperty('--preview-link', link);
            card.style.setProperty('--preview-toast', toast);

            document.getElementById('previewSwatch').style.background = primary;
            document.getElementById('previewHexLabel').textContent = primary;
        }

        function darkenHex(hex, percent) {
            hex = hex.replace('#', '');
            const num = parseInt(hex, 16);
            let r = (num >> 16) & 0xff;
            let g = (num >> 8) & 0xff;
            let b = num & 0xff;
            const factor = 1 - (percent / 100);
            r = Math.round(r * factor);
            g = Math.round(g * factor);
            b = Math.round(b * factor);
            return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        }

        fields.forEach(syncPair);
        updatePreview();

        document.getElementById('suggestHoverBtn').addEventListener('click', function () {
            const primary = document.getElementById('primary_color').value;
            if (!/^#[0-9A-Fa-f]{6}$/.test(primary)) return;
            const hover = darkenHex(primary, 18);
            document.getElementById('primary_hover_color').value = hover;
            document.getElementById('primary_hover_color_picker').value = hover;
            updatePreview();
        });

        const resetForm = document.getElementById('colorsResetForm');
        resetForm.addEventListener('submit', function (e) {
            e.preventDefault();
            window.showAppConfirm({
                title: @json(__('Reset colors?')),
                text: @json(__('All colors will go back to the default theme (#308e87).')),
                icon: 'fas fa-undo',
                confirmText: @json(__('Yes, reset'))
            }).then(function (ok) {
                if (ok) resetForm.submit();
            });
        });
    })();
</script>
@endpush
