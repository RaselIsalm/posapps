<div class="text-center mb-4">
    <h6 class="text-muted text-uppercase small ls-2 mb-1">Gift Card Number</h6>
    <h2 class="fw-bold text-dark" style="letter-spacing: 2px;">{{ $giftCard->card_no }}</h2>
    <span class="badge {{ $giftCard->status == 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3">
        {{ ucfirst($giftCard->status) }}
    </span>
</div>

<div class="row g-3 mb-4">
    <div class="col-4 text-center border-end">
        <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Original Value</small>
        <h5 class="fw-bold mb-0 text-dark">{{ \App\Helpers\CurrencyHelper::format($giftCard->value, 2) }}</h5>
    </div>
    <div class="col-4 text-center border-end">
        <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Selling Price</small>
        <h5 class="fw-bold mb-0 text-primary">{{ \App\Helpers\CurrencyHelper::format($giftCard->price, 2) }}</h5>
    </div>
    <div class="col-4 text-center">
        <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Current Balance</small>
        <h5 class="fw-bold mb-0 text-success">{{ \App\Helpers\CurrencyHelper::format($giftCard->balance, 2) }}</h5>
    </div>
</div>

<div class="bg-light p-3 rounded mb-3">
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted small">Customer:</span>
        <span class="fw-bold text-dark">
            {{ $giftCard->customer ? $giftCard->customer->name : 'Walk-in Customer' }}
        </span>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted small">Created By:</span>
        <span class="fw-bold text-dark">{{ $giftCard->createdBy->name ?? 'System' }}</span>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted small">Created Date:</span>
        <span class="fw-bold text-dark">{{ $giftCard->created_at->format('d M, Y h:i A') }}</span>
    </div>
    <div class="d-flex justify-content-between">
        <span class="text-muted small">Expiry Date:</span>
        <span class="fw-bold text-danger">{{ $giftCard->expiry_date->format('d M, Y') }}</span>
    </div>
</div>
