@extends('backend.layout.main')

@section('title', 'Fraud Check API Configuration')

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Fraud Check API
        @endslot
        @slot('subtitle')
            Configure your Fraud Check API settings
        @endslot
        <li class="breadcrumb-item">API Settings</li>
        <li class="breadcrumb-item active">Fraud Check API</li>
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">

                <div class="card">
                    <div class="card-header pb-0">
                        <h4>Fraud Check API Configuration</h4>
                        <p class="mt-1 text-muted">The field labels marked with * are required input fields.</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('fraud-check-setting.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">API Key *</label>
                                    <input class="form-control" type="text" name="api_key" 
                                        value="{{ $setting->api_key ?? '' }}" placeholder="Enter your BDCourier API Key">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">API URL *</label>
                                    <input class="form-control" type="text" name="api_url" 
                                        value="{{ $setting->api_url ?? '' }}" placeholder="https://api.bdcourier.com/api/fraud-check">
                                    <small class="text-info mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Example: <code>https://api.bdcourier.com/api/fraud-check</code></small>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check checkbox-primary">
                                        <input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" 
                                            {{ ($setting->is_active ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Turn on fraud check</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label small fw-bold">Test with a phone number</label>
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <input class="form-control" type="text" id="fraud_test_phone"
                                            placeholder="01XXXXXXXXX" maxlength="15">
                                        <button class="btn btn-outline-primary fw-bold px-4" type="button" id="fraudTestBtn">
                                            Test connection
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1">Save settings first, then test. The same check runs when you ship an order.</small>
                                    <div id="fraudTestResult" class="mt-2 small"></div>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button class="btn btn-primary px-5 py-2 shadow-sm fw-bold" type="submit" id="submitBtn">
                                        <i class="fas fa-save me-2"></i> Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('form').on('submit', function() {
            let btn = $(this).find('button[type="submit"]');
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...').prop('disabled', true);
        });

        $('#fraudTestBtn').on('click', function () {
            const phone = $('#fraud_test_phone').val();
            const btn = $(this);
            const original = btn.html();
            if (!phone) {
                $('#fraudTestResult').html('<span class="text-danger">Enter a phone number to test.</span>');
                return;
            }
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Testing...');
            $('#fraudTestResult').html('<span class="text-muted">Checking...</span>');
            $.ajax({
                url: "{{ route('fraud-check.ajax.test') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    phone: phone
                },
                success: function (res) {
                    if (res.success && res.risk) {
                        $('#fraudTestResult').html('<span class="text-success fw-bold">Connected.</span> ' + (res.risk.label || '') + ' — ' + (res.risk.hint || ''));
                    } else {
                        $('#fraudTestResult').html('<span class="text-warning">' + (res.message || @json(__('No record for this number, but the API responded.'))) + '</span>');
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : @json(__('Test failed. Check the API key, URL, and Active switch.'));
                    $('#fraudTestResult').html('<span class="text-danger">' + msg + '</span>');
                },
                complete: function () {
                    btn.prop('disabled', false).html(original);
                }
            });
        });
    });
</script>
@endpush
