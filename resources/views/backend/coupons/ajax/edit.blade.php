<form id="editCouponForm" action="{{ route('coupons.update', $coupon->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Coupon Code</label>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase fw-bold" id="edit_code" value="{{ $coupon->code }}" name="code"
                    required>
                <button type="button" class="btn btn-primary" onclick="generateRandomCodeEdit()">Auto</button>
            </div>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Type</label>
            <select class="form-select" id="edit_type" name="type" required onchange="toggleEditMaxDiscount()">
                <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Fixed Amount ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                <option value="percent" {{ $coupon->type == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
            </select>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Amount / Value</label>
            <input type="number" step="0.01" class="form-control" value="{{ $coupon->amount }}" name="amount"
                required>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Min Purchase</label>
            <input type="number" step="0.01" class="form-control" value="{{ $coupon->min_purchase }}"
                name="min_purchase">
        </div>

        <div class="col-md-6 col-12 mb-3" id="edit_max_discount_div"
            style="{{ $coupon->type == 'fixed' ? 'display:none;' : '' }}">
            <label class="form-label small fw-bold text-muted">Max Discount</label>
            <input type="number" step="0.01" class="form-control" id="edit_max_discount"
                value="{{ $coupon->max_discount }}" name="max_discount" placeholder="For % only">
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Usage Limit</label>
            <input type="number" class="form-control" value="{{ $coupon->usage_limit }}" name="usage_limit">
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">Start Date</label>
            <input type="date" class="form-control" value="{{ $coupon->start_date }}" name="start_date" required>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label class="form-label small fw-bold text-muted">End Date</label>
            <input type="date" class="form-control" value="{{ $coupon->end_date }}" name="end_date" required>
        </div>

        <div class="col-12 mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" value="1"
                    {{ $coupon->status == 1 ? 'checked' : '' }}>
                <label class="form-check-label">Active Status</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-3">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Changes</button>
    </div>
</form>

<script>
    function toggleEditMaxDiscount() {
        const type = document.getElementById('edit_type').value;
        const maxDiv = document.getElementById('edit_max_discount_div');
        if (type === 'percent') {
            maxDiv.style.display = 'block';
        } else {
            maxDiv.style.display = 'none';
        }
    }

    function generateRandomCodeEdit() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('edit_code').value = result;
    }
</script>
