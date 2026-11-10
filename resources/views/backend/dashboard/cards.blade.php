<div class="row g-3 mb-3">
    @php
    $cards1 = [
        ['label'=>'Total Customer','key'=>'totalCustomers','value'=>number_format($totalCustomers),'icon'=>'fas fa-users','color'=>'#308e87','bg'=>'rgba(48,142,135,0.12)'],
        ['label'=>'Total Supplier','key'=>'totalSuppliers','value'=>number_format($totalSuppliers),'icon'=>'fas fa-truck','color'=>'#0ea5e9','bg'=>'rgba(14,165,233,0.1)'],
        ['label'=>'Total Sale','key'=>'totalSale','value'=>\App\Helpers\CurrencyHelper::format($totalSale,2),'icon'=>'fas fa-chart-line','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)'],
        ['label'=>'Sale Return','key'=>'totalSaleReturn','value'=>\App\Helpers\CurrencyHelper::format($totalSaleReturn,2),'icon'=>'fas fa-undo','color'=>'#f97316','bg'=>'rgba(249,115,22,0.1)'],
        ['label'=>'Total Purchase','key'=>'totalPurchase','value'=>\App\Helpers\CurrencyHelper::format($totalPurchase,2),'icon'=>'fas fa-shopping-cart','color'=>'#0f766e','bg'=>'rgba(15,118,110,0.12)'],
        ['label'=>'Purchase Return','key'=>'totalPurchaseReturn','value'=>\App\Helpers\CurrencyHelper::format($totalPurchaseReturn,2),'icon'=>'fas fa-exchange-alt','color'=>'#ec4899','bg'=>'rgba(236,72,153,0.1)'],
    ];
    @endphp
    @foreach($cards1 as $c)
    <div class="col-xl-2 col-md-4 col-sm-6 col-6">
        <div class="dash-card">
            <div class="dash-card-icon" style="background:{{ $c['bg'] }};color:{{ $c['color'] }}">
                <i class="{{ $c['icon'] }}"></i>
            </div>
            <div class="dash-card-val" style="color:{{ $c['color'] }}" data-key="{{ $c['key'] }}">{{ $c['value'] }}</div>
            <div class="dash-card-label">{{ __($c['label']) }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    @php
    $cards2 = [
        ['label'=>'Total Paid','key'=>'totalPaid','value'=>\App\Helpers\CurrencyHelper::format($totalPaid,2),'icon'=>'fas fa-check-circle','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)'],
        ['label'=>'Total Due','key'=>'totalDue','value'=>\App\Helpers\CurrencyHelper::format($totalDue,2),'icon'=>'fas fa-exclamation-circle','color'=>'#ef4444','bg'=>'rgba(239,68,68,0.1)'],
        ['label'=>'Total Due Receive','key'=>'totalDueReceive','value'=>\App\Helpers\CurrencyHelper::format($totalDueReceive,2),'icon'=>'fas fa-hand-holding-usd','color'=>'#0ea5e9','bg'=>'rgba(14,165,233,0.1)'],
        ['label'=>'Total Purchase Paid','key'=>'totalPurchasePaid','value'=>\App\Helpers\CurrencyHelper::format($totalPurchasePaid,2),'icon'=>'fas fa-money-bill-wave','color'=>'#14b8a6','bg'=>'rgba(20,184,166,0.12)'],
        ['label'=>'Total Purchase Due','key'=>'totalPurchaseDue','value'=>\App\Helpers\CurrencyHelper::format($totalPurchaseDue,2),'icon'=>'fas fa-file-invoice-dollar','color'=>'#f97316','bg'=>'rgba(249,115,22,0.1)'],
        ['label'=>'Total Expense','key'=>'totalExpense','value'=>\App\Helpers\CurrencyHelper::format($totalExpense,2),'icon'=>'fas fa-receipt','color'=>'#ec4899','bg'=>'rgba(236,72,153,0.1)'],
    ];
    @endphp
    @foreach($cards2 as $c)
    <div class="col-xl-2 col-md-4 col-sm-6 col-6">
        <div class="dash-card">
            <div class="dash-card-icon" style="background:{{ $c['bg'] }};color:{{ $c['color'] }}">
                <i class="{{ $c['icon'] }}"></i>
            </div>
            <div class="dash-card-val" style="color:{{ $c['color'] }}" data-key="{{ $c['key'] }}">{{ $c['value'] }}</div>
            <div class="dash-card-label">{{ __($c['label']) }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6 col-md-6 col-sm-6 col-6">
        <div class="dash-card">
            <div class="dash-card-icon" style="background:rgba(48,142,135,0.12);color:#308e87"><i class="fas fa-trophy"></i></div>
            <div class="dash-card-val" style="color:#308e87" data-key="profit">{{ \App\Helpers\CurrencyHelper::format($profit, 2) }}</div>
            <div class="dash-card-label">{{ __('Profit') }}</div>
        </div>
    </div>
    <div class="col-xl-6 col-md-6 col-sm-6 col-6">
        <div class="dash-card">
            <div class="dash-card-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9"><i class="fas fa-coins"></i></div>
            <div class="dash-card-val" style="color:#0ea5e9" data-key="totalSalary">{{ \App\Helpers\CurrencyHelper::format($totalSalary, 2) }}</div>
            <div class="dash-card-label">{{ __('Total Salary') }}</div>
        </div>
    </div>
</div>
