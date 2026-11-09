@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Dashboard
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/vendors/select2.css" />
    <style>
        #timezone + .select2-container { width: 100% !important; }
    </style>
@endpush


@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')General Settings @endslot
    @slot('subtitle')Manage and configure your system's fundamental preferences and parameters. @endslot
<li class="breadcrumb-item">Settings</li>
<li class="breadcrumb-item active">General Settings</li>
@endcomponent
<div class="container-fluid default-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.generalUpdate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" class="form-control mt-1" id="company_name" name="company_name" value="{{ $general_settings['company_name'] ?? old('company_name') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="company_phone">Company Phone</label>
                                    <input type="text" class="form-control mt-1" id="company_phone" name="company_phone" value="{{ $general_settings['company_phone'] ?? old('company_phone') }}" oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="company_email">Company Email</label>
                                    <input type="email" class="form-control mt-1" id="company_email" name="company_email" value="{{ $general_settings['company_email'] ?? old('company_email') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <div class="gs-field-group mt-1">
                                        <select class="form-control" id="currency" name="currency">
                                            <option value="" selected disabled>Select Currency</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->code }}" {{ (isset($general_settings['currency']) && $general_settings['currency'] == $currency->code) ? 'selected' : '' }}>
                                                    {{ $currency->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @can('create_currencies')
                                            <button type="button" class="btn btn-outline-primary btn-gs-add"
                                                onclick="openGsCurrencyModal()" title="Add New Currency">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    @php
                                        $currentTz = $general_settings['timezone'] ?? 'Europe/Madrid';
                                        $spainZones = [
                                            'Europe/Madrid' => 'Europe/Madrid (Spain)',
                                            'Atlantic/Canary' => 'Atlantic/Canary (Canary Islands)',
                                        ];
                                        $europeZones = collect(timezone_identifiers_list(\DateTimeZone::EUROPE))
                                            ->reject(fn ($id) => isset($spainZones[$id]))
                                            ->values();
                                        $otherZones = collect(timezone_identifiers_list())
                                            ->reject(fn ($id) => str_starts_with($id, 'Europe/') || isset($spainZones[$id]))
                                            ->values();
                                    @endphp
                                    <select class="form-control mt-1 select2-timezone" id="timezone" name="timezone" data-placeholder="{{ __('Search timezone...') }}">
                                        <optgroup label="Spain">
                                            @foreach ($spainZones as $id => $label)
                                                <option value="{{ $id }}" {{ $currentTz === $id ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Europe">
                                            @foreach ($europeZones as $id)
                                                <option value="{{ $id }}" {{ $currentTz === $id ? 'selected' : '' }}>{{ $id }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Other">
                                            @foreach ($otherZones as $id)
                                                <option value="{{ $id }}" {{ $currentTz === $id ? 'selected' : '' }}>{{ $id }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_format">Date Format</label>
                                    <select class="form-control mt-1" id="date_format" name="date_format">
                                        <option value="" selected disabled>Select Date Format</option>
                                        <option value="d-m-Y" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'd-m-Y') ? 'selected' : '' }}>d-m-Y (01-01-2022)</option>
                                        <option value="m-d-Y" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'm-d-Y') ? 'selected' : '' }}>m-d-Y (01-01-2022)</option>
                                        <option value="Y-m-d" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'Y-m-d') ? 'selected' : '' }}>Y-m-d (2022-01-01)</option>
                                        <option value="Y-d-m" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'Y-d-m') ? 'selected' : '' }}>Y-d-m (2022-01-01)</option>
                                        <option value="m-Y-d" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'm-Y-d') ? 'selected' : '' }}>m-Y-d (01-2022-01)</option>
                                        <option value="d/Y/m" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'd/Y/m') ? 'selected' : '' }}>d/Y/m (01/2022/01)</option>
                                        <option value="Y/m/d" {{ (isset($general_settings['date_format']) && $general_settings['date_format'] == 'Y/m/d') ? 'selected' : '' }}>Y/m/d (2022/01/01)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="default_tax">Default Tax (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control mt-1" id="default_tax" name="default_tax" value="{{ max(0, (float) ($general_settings['default_tax'] ?? 5)) }}" oninput="if (this.value !== '' && Number(this.value) < 0) this.value = 0;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pos_print_mode">POS Print Mode</label>
                                    <select class="form-control mt-1" id="pos_print_mode" name="pos_print_mode">
                                        <option value="manual" {{ (isset($general_settings['pos_print_mode']) && $general_settings['pos_print_mode'] == 'manual') ? 'selected' : '' }}>Manual Print (Preview & Prompt)</option>
                                        <option value="auto" {{ (isset($general_settings['pos_print_mode']) && $general_settings['pos_print_mode'] == 'auto') ? 'selected' : '' }}>Auto Print (Direct Print)</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <strong>Manual</strong> = confirm then print.
                                        <strong>Auto</strong> = print starts after sale (no confirm).
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pos_printer_type">POS Printer Type</label>
                                    <select class="form-control mt-1" id="pos_printer_type" name="pos_printer_type">
                                        <option value="thermal" {{ (isset($general_settings['pos_printer_type']) && $general_settings['pos_printer_type'] == 'thermal') ? 'selected' : '' }}>Thermal Printer</option>
                                        <option value="a4" {{ (isset($general_settings['pos_printer_type']) && $general_settings['pos_printer_type'] == 'a4') ? 'selected' : '' }}>A4 Printer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_payment_method">Default Payment Method</label>
                                    @php
                                        $currentPaymentMethod = (string) ($general_settings['default_payment_method'] ?? '');
                                        if ($currentPaymentMethod === '') {
                                            $currentPaymentMethod = (string) (optional($accounts->firstWhere('is_default', 1))->id ?? '');
                                        }
                                    @endphp
                                    <div class="gs-field-group mt-1">
                                        <select class="form-control" id="default_payment_method" name="default_payment_method">
                                            <option value="">Select default account</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}" {{ (string) $account->id === $currentPaymentMethod ? 'selected' : '' }}>
                                                    {{ $account->name }}{{ $account->type ? ' ('.$account->type.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @can('create_accounts')
                                            <button type="button" class="btn btn-outline-primary btn-gs-add"
                                                onclick="openGsAccountModal()" title="Add New Payment Method">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                    <small class="text-muted">Automatically selected at checkout.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="thermal_printer_size">Thermal Printer Size</label>
                                    @php
                                        $thermalSizes = \App\Helpers\InvoiceHelper::thermalSizes();
                                        $currentThermalSize = $general_settings['thermal_printer_size'] ?? \App\Helpers\InvoiceHelper::DEFAULT_THERMAL_SIZE;
                                    @endphp
                                    <select class="form-control mt-1" id="thermal_printer_size" name="thermal_printer_size">
                                        @foreach ($thermalSizes as $size => $label)
                                            <option value="{{ $size }}" {{ $currentThermalSize === $size ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Used as the default paper width for all thermal invoice prints (industry-standard roll sizes).</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">Company Logo</label>
                                    <input type="file" class="form-control mt-1 mb-2" id="logo" name="logo" accept="image/*">
                                    <input type="hidden" name="remove_logo" id="remove_logo" value="0">

                                    @if (isset($general_settings['logo']) && $general_settings['logo'])
                                        <div id="logo_saved_container" class="border rounded p-2 bg-light d-inline-block">
                                            <img id="logo_preview_saved" src="{{ asset('storage/settings/'.$general_settings['logo']) }}" alt="Company logo" style="max-height: 80px; max-width: 150px; object-fit: contain; display: block;">
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeLogoBtn">Remove logo</button>
                                        </div>
                                    @endif

                                    <div id="logo_preview_container" class="border rounded p-2 bg-light" style="display:none;">
                                        <img id="logo_preview" src="" alt="Logo preview" style="max-height: 80px; max-width: 150px; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="favicon">Company Favicon</label>
                                    <input type="file" class="form-control mt-1 mb-2" id="favicon" name="favicon" accept="image/*">
                                    <input type="hidden" name="remove_favicon" id="remove_favicon" value="0">

                                    @if (isset($general_settings['favicon']) && $general_settings['favicon'])
                                        <div id="favicon_saved_container" class="border rounded p-2 bg-light d-inline-block">
                                            <img id="favicon_preview_saved" src="{{ asset('storage/settings/'.$general_settings['favicon']) }}" alt="Favicon" style="max-height: 40px; max-width: 40px; object-fit: contain; display: block;">
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFaviconBtn">Remove favicon</button>
                                        </div>
                                    @endif

                                    <div id="favicon_preview_container" class="border rounded p-2 bg-light" style="display:none;">
                                        <img id="favicon_preview" src="" alt="Favicon preview" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            (function () {
                                function hideSavedImage(kind) {
                                    var flag = document.getElementById('remove_' + kind);
                                    var box = document.getElementById(kind + '_saved_container');
                                    var file = document.getElementById(kind);
                                    if (flag) {
                                        flag.value = '1';
                                    }
                                    if (file) {
                                        file.value = '';
                                    }
                                    if (box) {
                                        box.hidden = true;
                                        box.style.setProperty('display', 'none', 'important');
                                    }
                                }
                                var logoBtn = document.getElementById('removeLogoBtn');
                                var faviconBtn = document.getElementById('removeFaviconBtn');
                                if (logoBtn) {
                                    logoBtn.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        hideSavedImage('logo');
                                    });
                                }
                                if (faviconBtn) {
                                    faviconBtn.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        hideSavedImage('favicon');
                                    });
                                }
                            })();
                        </script>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backend.settings.partials.quick_add_currency_account')

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
        $('.general-settings-menu').addClass('active');

        if (typeof $.fn.select2 !== 'undefined') {
            $('#timezone').select2({
                width: '100%',
                placeholder: @json(__('Search timezone...')),
                allowClear: false,
            });
        }

        $('#logo').on('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            $('#logo_preview').attr('src', window.URL.createObjectURL(file));
            $('#logo_preview_container').css('display', 'inline-block');
            $('#logo_saved_container').hide();
            $('#remove_logo').val('0');
        });
        $('#removeLogoBtn').on('click', function () {
            $('#remove_logo').val('1');
            $('#logo_saved_container').hide();
            $('#logo').val('');
        });

        $('#favicon').on('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            $('#favicon_preview').attr('src', window.URL.createObjectURL(file));
            $('#favicon_preview_container').css('display', 'inline-block');
            $('#favicon_saved_container').hide();
            $('#remove_favicon').val('0');
        });
        $('#removeFaviconBtn').on('click', function () {
            $('#remove_favicon').val('1');
            $('#favicon_saved_container').hide();
            $('#favicon').val('');
        });
    </script>
@endpush
