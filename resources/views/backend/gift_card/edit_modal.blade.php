<form id="editGiftCardForm" action="{{ route('gift-card.update', $giftCard->id) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label small fw-bold text-muted">Card Number</label>
        <input type="text" name="card_no" class="form-control fw-bold" value="{{ $giftCard->card_no }}" required>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label small fw-bold text-muted">Original Value</label>
            <input type="number" step="0.01" class="form-control" value="{{ $giftCard->value }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-bold text-muted">Current Balance</label>
            <input type="number" step="0.01" class="form-control text-success fw-bold" value="{{ $giftCard->balance }}" readonly>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label small fw-bold text-muted">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control"
                value="{{ $giftCard->expiry_date->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-bold text-muted">Status</label>
            <select name="status" class="form-select pb-2">
                <option value="active" {{ $giftCard->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="used" {{ $giftCard->status == 'used' ? 'selected' : '' }}>Used</option>
                <option value="expired" {{ $giftCard->status == 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="inactive" {{ $giftCard->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label small fw-bold text-muted">Assigned Customer</label>
        <div class="input-group gc-field-group">
            <select name="customer_id" class="form-select">
                <option value="">Walk-in Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $giftCard->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->phone }})
                    </option>
                @endforeach
            </select>
            @can('create_customers')
                <button class="btn btn-outline-primary btn-gc-add" type="button"
                    onclick="openGcCustomerModal('#editGiftCardForm select[name=\'customer_id\']')"
                    title="Add Customer">
                    <i class="fas fa-plus"></i>
                </button>
            @endcan
        </div>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i> Update Changes
        </button>
    </div>
</form>
