@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Reward Point Settings
@endsection

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Reward Point Settings
        @endslot
        @slot('subtitle')
            Manage your system reward point settings.
        @endslot
        <li class="breadcrumb-item active">Reward Point Settings</li>
    @endcomponent
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-gift me-2"></i>Reward Point Settings</h5>
                    </div>
                    <div class="card-body p-4">



                        <form action="{{ route('reward-points.update') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold">Enable Reward System</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ ($setting->is_active ?? 1) == 1 ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0" {{ ($setting->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Minimum Purchase Amount ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</label>
                                <input type="number" step="0.01" name="min_purchase_amount" class="form-control"
                                    value="{{ $setting->min_purchase_amount ?? 1000 }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Point Earning Rate ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</label>
                                <input type="number" step="0.01" name="point_earning_rate" class="form-control"
                                    value="{{ $setting->point_earning_rate ?? 100 }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Minimum Redeemable Point</label>
                                <input type="number" name="min_redeem_point" class="form-control"
                                    value="{{ $setting->min_redeem_point ?? 100 }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Point Redemption Rate ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</label>
                                <input type="number" step="0.01" name="point_redemption_rate" class="form-control"
                                    value="{{ $setting->point_redemption_rate ?? 1 }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
