<div class="recharge-modal-wrapper">
    <!-- Card Info Header -->
    <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light border shadow-sm">
        <div class="flex-shrink-0 me-3">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary shadow-sm"
                style="width: 54px; height: 54px; box-shadow: 0 0 15px rgba(var(--primary-rgb, 48, 142, 135), 0.2) !important;">
                <i class="fas fa-bolt text-white fs-4"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <h6 class="text-muted text-uppercase mb-0 ls-1 fw-bold" style="font-size: 11px;">Recharging Card</h6>
            <h4 class="fw-bold text-dark mb-0 ls-1">{{ $giftCard->card_no }}</h4>
            <div class="d-flex align-items-center gap-2 mt-1">
                <i class="fas fa-user-circle text-muted" style="font-size: 12px;"></i>
                <span class="text-muted fw-semibold" style="font-size: 12px;">
                    {{ $giftCard->customer ? $giftCard->customer->name : 'Walk-in Customer' }}
                </span>
            </div>
        </div>
        <div class="text-end">
            <span
                class="badge bg-soft-success text-success border border-success border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                {{ strtoupper($giftCard->status) }}
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="p-3 rounded-4 border bg-white text-center h-100 shadow-sm transition-hover">
                <span class="text-muted d-block mb-1 fw-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Face Value</span>
                <h5 class="fw-bold mb-0 text-dark">{{ \App\Helpers\CurrencyHelper::format($giftCard->value, 2) }}</h5>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 rounded-4 border bg-white text-center h-100 shadow-sm transition-hover border-success border-opacity-25"
                style="background: linear-gradient(to bottom right, #fff, #f8fffb);">
                <span class="text-success d-block mb-1 fw-bold text-uppercase"
                    style="font-size: 10px; letter-spacing: 0.5px;">Current Balance</span>
                <h5 class="fw-bold mb-0 text-success">{{ \App\Helpers\CurrencyHelper::format($giftCard->balance, 2) }}</h5>
            </div>
        </div>
    </div>

    @if ($giftCard->expiry_date < now() && $giftCard->status !== 'expired')
        <div class="alert alert-soft-danger border-0 rounded-4 d-flex align-items-center p-3 mb-4 shadow-sm">
            <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
            </div>
            <div>
                <strong class="d-block text-danger small">Card Expired</strong>
                <p class="mb-0 text-muted small">Please extend the expiry date below while recharging.</p>
            </div>
        </div>
    @endif

    <form id="rechargeGiftCardForm" action="{{ route('gift-card.recharge', $giftCard->id) }}" class="recharge-form">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-flex justify-content-between">
                <span>RECHARGE AMOUNT <span class="text-danger">*</span></span>
                <span class="text-primary cursor-pointer add-quick-amt" style="font-size: 10px;">+ {{ \App\Helpers\CurrencyHelper::getSymbol() }}50</span>
            </label>
            <div class="input-group input-group-lg custom-input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-4 text-success fw-bold">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                <input type="number" step="0.01" min="1" name="amount" id="rechargeAmount"
                    class="form-control border-start-0 rounded-end-4 fw-bold text-success fs-4" placeholder="0.00"
                    required autofocus style="padding-left: 0;">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label class="form-label fw-bold text-dark small mb-2">PAYMENT METHOD <span
                        class="text-danger">*</span></label>
                <div class="input-group gc-field-group">
                    <div class="position-relative flex-grow-1">
                        <i class="fas fa-university position-absolute text-muted"
                            style="left: 15px; top: 12px; z-index: 10;"></i>
                        <select name="account_id" class="form-select rounded-start-4 ps-5" required
                            style="padding-top: 10px; padding-bottom: 10px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="">Choose receiving account</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @can('create_accounts')
                        <button class="btn btn-outline-primary btn-gc-add" type="button"
                            onclick="openGcAccountModal('#rechargeGiftCardForm select[name=\'account_id\']')"
                            title="Add Payment Method"
                            style="height: auto; border-top-right-radius: 1rem; border-bottom-right-radius: 1rem;">
                            <i class="fas fa-plus"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark small mb-2">EXTEND EXPIRY</label>
                <div class="position-relative">
                    <i class="fas fa-calendar-alt position-absolute text-muted"
                        style="left: 15px; top: 12px; z-index: 10;"></i>
                    <input type="date" name="new_expiry_date" class="form-control rounded-4 ps-5"
                        value="{{ $giftCard->expiry_date->format('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                        style="padding-top: 10px; padding-bottom: 10px;">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark small mb-2">TRANSACTION NOTE</label>
                <div class="position-relative">
                    <i class="fas fa-pencil-alt position-absolute text-muted"
                        style="left: 15px; top: 12px; z-index: 10;"></i>
                    <input type="text" name="note" class="form-control rounded-4 ps-5"
                        placeholder="Optional details" style="padding-top: 10px; padding-bottom: 10px;">
                </div>
            </div>
        </div>

        <div class="new-balance-card p-3 rounded-4 mb-4 border-dashed border-success border-opacity-50"
            style="background: rgba(25, 135, 84, 0.03); border: 2px dashed rgba(25, 135, 84, 0.2) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted fw-bold d-block mb-1"
                        style="font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase;">Estimated New
                        Balance</span>
                    <span class="text-dark small">Current + Recharge amount</span>
                </div>
                <div class="text-end">
                    <h3 class="fw-bold text-success mb-0" id="newBalancePreview">
                        {{ \App\Helpers\CurrencyHelper::format($giftCard->balance, 2) }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary fw-bold py-2 btn-recharge-submit">
                <i class="fas fa-check-circle me-2"></i> CONFIRM RECHARGE
            </button>
            <button type="button" class="btn btn-outline-secondary py-2" data-bs-dismiss="modal">
                Cancel
            </button>
        </div>
    </form>
</div>

<style>
    .ls-1 {
        letter-spacing: 0.5px;
    }

    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .transition-hover {
        transition: all 0.3s ease;
    }

    .transition-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06) !important;
    }

    .rounded-4 {
        border-radius: 1rem !important;
    }

    .custom-input-group .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }

    .custom-input-group {
        border: 2px solid #e9ecef;
        border-radius: 1rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .custom-input-group:focus-within {
        border-color: var(--primary-color, #308e87);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 48, 142, 135), 0.1);
    }

    .alert-soft-danger {
        background-color: #fff5f8;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    (function() {
        const input = document.getElementById('rechargeAmount');
        const preview = document.getElementById('newBalancePreview');
        const currentBalance = {{ $giftCard->balance }};

        input?.addEventListener('input', function() {
            const addAmount = parseFloat(this.value) || 0;
            const newBalance = currentBalance + addAmount;
            preview.innerText = formatCurrency(newBalance.toLocaleString)(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (addAmount > 0) {
                preview.classList.add('text-success');
                preview.parentElement.parentElement.parentElement.style.borderColor =
                    'rgba(25, 135, 84, 0.5)';
            } else {
                preview.parentElement.parentElement.parentElement.style.borderColor =
                    'rgba(25, 135, 84, 0.2)';
            }
        });

        document.querySelector('.add-quick-amt')?.addEventListener('click', function() {
            const val = (parseFloat(input.value) || 0) + 50;
            input.value = val;
            input.dispatchEvent(new Event('input'));
        });
    })();
</script>
