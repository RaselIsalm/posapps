<form action="{{ route('accounts.update', $account->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-6 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $account->name }}"
                placeholder="Account Name" required>
        </div>
        <div class="col-6 mb-3">
            <label for="account_number" class="form-label">Account Number <span id="account_number_required" class="text-danger {{ $account->type === 'cash' ? 'd-none' : '' }}">*</span></label>
            <input type="text" class="form-control" id="account_number" name="account_number"
                value="{{ $account->account_number }}" placeholder="{{ $account->type === 'cash' ? 'Not required for cash' : 'Account Number' }}" {{ $account->type === 'cash' ? '' : 'required' }}>
            <small id="account_number_hint" class="text-muted {{ $account->type === 'cash' ? '' : 'd-none' }}">Cash accounts do not need an account number.</small>
        </div>
        <div class="col-6 mb-3">
            <label for="initial_balance" class="form-label">Initial Balance</label>
            <input type="number" step="0.01" class="form-control" id="initial_balance" name="initial_balance"
                value="{{ $account->initial_balance }}" placeholder="Initial Balance" required>
        </div>
        <div class="col-6 mb-3">
            <label for="type" class="form-label">Select Type</label>
            <select class="form-select" id="type" name="type" required>
                <option value="bank" {{ $account->type == 'bank' ? 'selected' : '' }}>Bank</option>
                <option value="cash" {{ $account->type == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="mobile banking" {{ $account->type == 'mobile banking' ? 'selected' : '' }}>Mobile Banking
                </option>
                <option value="digital wallet" {{ $account->type == 'digital wallet' ? 'selected' : '' }}>Digital Wallet
                </option>
                <option value="other" {{ $account->type == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" placeholder="Note" rows="2">{{ $account->note }}</textarea>
        </div>
        <div class="col-12 mb-4">
            <div class="account-settings">
                <div class="account-setting-row">
                    <div class="account-setting-copy">
                        <label for="is_active">Active</label>
                        <p>Available at checkout and in transactions.</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                            name="is_active" style="width: 44px; height: 22px;"
                            {{ $account->is_active ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="account-setting-row">
                    <div class="account-setting-copy">
                        <label for="is_default">Default payment method</label>
                        <p>Automatically selected at checkout.</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_default"
                            name="is_default" style="width: 44px; height: 22px;"
                            {{ $account->is_default ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="account-setting-row">
                    <div class="account-setting-copy">
                        <label for="allow_manual_amount">Enter amount yourself</label>
                        <p>Turn on to type the paid amount. Leave off to use the product total.</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            id="allow_manual_amount" name="allow_manual_amount" style="width: 44px; height: 22px;"
                            {{ $account->allow_manual_amount ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
<script>
    (function () {
        const typeEl = document.getElementById('type');
        const numberEl = document.getElementById('account_number');
        const requiredMark = document.getElementById('account_number_required');
        const hintEl = document.getElementById('account_number_hint');
        if (!typeEl || !numberEl) return;

        const toggleAccountNumber = function () {
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        };

        typeEl.addEventListener('change', toggleAccountNumber);
        toggleAccountNumber();
    })();
</script>
