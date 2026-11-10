<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom-0 pb-0 pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-landmark text-primary me-2"></i>{{ __('Assets') }}</h6>
            </div>
            <div class="card-body px-4 pt-3">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">{{ __('Receivable Customer Due') }}</td><td class="fw-semibold text-end" data-key="receivableCustomerDue">{{ \App\Helpers\CurrencyHelper::format($receivableCustomerDue, 2) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Total Account Balance') }}</td><td class="fw-semibold text-end" data-key="totalAccountBalance">{{ \App\Helpers\CurrencyHelper::format($totalAccountBalance, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom-0 pb-0 pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-balance-scale text-warning me-2"></i>{{ __('Liabilities') }}</h6>
            </div>
            <div class="card-body px-4 pt-3">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">{{ __('Supplier Due') }}</td><td class="fw-semibold text-end" data-key="supplierDue">{{ \App\Helpers\CurrencyHelper::format($supplierDue, 2) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Customer Advance') }}</td><td class="fw-semibold text-end" data-key="customerAdvance">{{ \App\Helpers\CurrencyHelper::format($customerAdvance, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom-0 pb-0 pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-exchange-alt text-success me-2"></i>{{ __('Cash In/Out') }}</h6>
            </div>
            <div class="card-body px-4 pt-3">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">{{ __('Total Cash In') }}</td><td class="fw-semibold text-success text-end" data-key="totalCashIn">{{ \App\Helpers\CurrencyHelper::format($totalCashIn, 2) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Total Cash Out') }}</td><td class="fw-semibold text-danger text-end" data-key="totalCashOut">{{ \App\Helpers\CurrencyHelper::format($totalCashOut, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
