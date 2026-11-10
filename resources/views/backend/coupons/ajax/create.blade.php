<form action="{{ route('coupons.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6 col-12 mb-3">
            <label for="code" class="form-label">Coupon Code</label>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase fw-bold" id="create_code" name="code"
                    placeholder="Ex: SUMMER2024" required>
                <button type="button" class="btn btn-primary" onclick="generateRandomCode()">Auto</button>
            </div>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-control" id="create_type" name="type" required onchange="toggleCreateMaxDiscount()">
                <option value="fixed" selected>Fixed Amount ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                <option value="percent">Percentage (%)</option>
            </select>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="amount" class="form-label">Amount / Value</label>
            <input type="number" step="0.01" class="form-control" id="create_amount" name="amount"
                placeholder="0.00" required>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="min_purchase" class="form-label">Min Purchase Amount</label>
            <input type="number" step="0.01" class="form-control" id="create_min_purchase" name="min_purchase"
                placeholder="0.00">
        </div>

        <div class="col-md-6 col-12 mb-3" id="create_max_discount_div" style="display:none;">
            <label for="max_discount" class="form-label">Max Discount Amount</label>
            <input type="number" step="0.01" class="form-control" id="create_max_discount" name="max_discount"
                placeholder="For % only">
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="usage_limit" class="form-label">Usage Limit</label>
            <input type="number" class="form-control" id="create_usage_limit" name="usage_limit"
                placeholder="Total usage limit (Optional)">
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="create_start_date" name="start_date"
                value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="create_end_date" name="end_date"
                value="{{ date('Y-m-d', strtotime('+1 month')) }}" required>
        </div>

        <div class="col-md-6 col-12 mb-3">
            <div class="row align-items-center pt-4">
                <div class="col-auto">
                    <label class="form-check-label fw-bold" for="create_status">Is Active</label>
                </div>
                <div class="col-auto">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="create_status"
                            name="status" value="1" checked style="width: 50px; height: 25px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
        </button>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-check me-1"></i> Create Coupon
        </button>
    </div>
</form>

{{-- Inline Script for interactivity --}}
<script>
    function toggleCreateMaxDiscount() {
        const type = document.getElementById('create_type').value;
        const maxDiv = document.getElementById('create_max_discount_div');
        const maxInput = document.getElementById('create_max_discount');

        if (type === 'percent') {
            maxDiv.style.display = 'block';
        } else {
            maxDiv.style.display = 'none';
            maxInput.value = '';
        }
    }

    function generateRandomCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('create_code').value = result;
    }
</script>
