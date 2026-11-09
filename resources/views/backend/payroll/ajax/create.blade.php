<form action="{{ route('payrolls.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <div class="input-group flex-nowrap pay-field-group">
                <select name="employee_id" class="form-control" required>
                    <option value="">Select Employee</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->phone }})</option>
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
                    <option value="{{ $month }}" {{ date('F') == $month ? 'selected' : '' }}>{{ $month }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Year <span class="text-danger">*</span></label>
            <select name="salary_year" class="form-control" required>
                @for ($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control calc-input" id="basic_salary" name="basic_salary"
                placeholder="0.00" required>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Total Allowance</label>
            <input type="number" step="0.01" class="form-control calc-input" id="total_allowance"
                name="total_allowance" value="0" placeholder="0.00">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Total Deduction</label>
            <input type="number" step="0.01" class="form-control calc-input" id="total_deduction"
                name="total_deduction" value="0" placeholder="0.00">
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label"><strong>Net Salary</strong></label>
            <input type="number" step="0.01" class="form-control bg-light" id="net_salary" name="net_salary"
                placeholder="0.00" readonly required style="font-weight: bold; color: black !important;">
        </div>

        <div class="col-12 col-md-6 mb-3">
            <label class="form-label" for="payment_method">Payment Account <span class="text-danger">*</span></label>
            <div class="input-group flex-nowrap pay-field-group">
                <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="">Select Account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" data-balance="{{ $account->current_balance }}">
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
            <label class="form-label">Transaction ID (Optional)</label>
            <input type="text" class="form-control" name="transaction_id" placeholder="TrxID">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Payment Date</label>
            <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="status">
                <option value="paid">Paid</option>
                <option value="pending" selected>Pending</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="note" rows="2" placeholder="Optional notes..."></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
        <button type="submit" id="submit_payroll_btn" class="btn btn-primary">Create Payroll</button>
    </div>
</form>

@include('backend.payroll.ajax.partials.quick_add')
