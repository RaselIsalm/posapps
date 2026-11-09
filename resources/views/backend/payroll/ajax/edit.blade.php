<form action="{{ route('payrolls.update', $payroll->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <div class="input-group flex-nowrap pay-field-group">
                <select name="employee_id" class="form-control" required>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $payroll->employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->phone }})
                        </option>
                    @endforeach
                </select>
                @can('create_employees')
                    <button class="btn btn-outline-primary btn-pay-add" type="button"
                        onclick="openPayEmployeeModal()" title="Add Employee"
                        aria-label="Add Employee">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Month <span class="text-danger">*</span></label>
            <select name="salary_month" class="form-control" required>
                @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                    <option value="{{ $month }}" {{ $payroll->salary_month == $month ? 'selected' : '' }}>
                        {{ $month }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Year <span class="text-danger">*</span></label>
            <select name="salary_year" class="form-control" required>
                @for ($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}" {{ $payroll->salary_year == $i ? 'selected' : '' }}>
                        {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control calc-input" id="basic_salary" name="basic_salary"
                value="{{ $payroll->basic_salary }}" placeholder="0.00" required>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Total Allowance</label>
            <input type="number" step="0.01" class="form-control calc-input" id="total_allowance"
                name="total_allowance" value="{{ $payroll->total_allowance }}" placeholder="0.00">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Total Deduction</label>
            <input type="number" step="0.01" class="form-control calc-input" id="total_deduction"
                name="total_deduction" value="{{ $payroll->total_deduction }}" placeholder="0.00">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label"><strong>Net Salary</strong></label>
            <input type="number" step="0.01" class="form-control bg-light" id="net_salary" name="net_salary"
                value="{{ $payroll->net_salary }}" placeholder="0.00" readonly required
                style="color: #000000 !important; font-weight: bold;">
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label" for="payment_method">Payment Account <span class="text-danger">*</span></label>
            <div class="input-group flex-nowrap pay-field-group">
                <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="">Select Account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" data-balance="{{ $account->current_balance }}"
                            {{ (string) $payroll->payment_method === (string) $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ \App\Helpers\CurrencyHelper::format($account->current_balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @can('create_accounts')
                    <button class="btn btn-outline-primary btn-pay-add" type="button"
                        onclick="openPayAccountModal()" title="Add Payment Account"
                        aria-label="Add Payment Account">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div id="balance_alert" class="alert alert-danger mt-2" style="display:none;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <span id="balance_alert_msg"></span>
        </div>
        <div id="balance_ok" class="alert alert-success mt-2" style="display:none;">
            <i class="fas fa-check-circle me-1"></i>
            <span id="balance_ok_msg"></span>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Transaction ID</label>
            <input type="text" class="form-control" name="transaction_id" value="{{ $payroll->transaction_id }}"
                placeholder="TrxID">
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Payment Date</label>
            <input type="date" class="form-control" name="payment_date" value="{{ $payroll->payment_date }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="status">
                <option value="paid" {{ $payroll->status == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ $payroll->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="cancelled" {{ $payroll->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Note">{{ $payroll->note }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Payroll</button>
    </div>
</form>
<script>
    $(document).ready(function() {
        checkBalance();
    });
</script>

@include('backend.payroll.ajax.partials.quick_add')
