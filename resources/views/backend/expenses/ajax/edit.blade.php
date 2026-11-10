<form action="{{ route('expenses.update', $expense->id) }}" method="POST" id="expenseEditForm">
    @csrf
    @method('PUT')
    <div id="expenseFormError" class="alert alert-danger d-none py-2 px-3 small" role="alert"></div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control" id="date"
                value="{{ old('date', \Carbon\Carbon::parse($expense->date)->format('Y-m-d')) }}" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="category_id" class="form-label">Select Category <span class="text-danger">*</span></label>
            <div class="input-group exp-field-group">
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @can('create_expenses_categories')
                    <button class="btn btn-outline-primary btn-exp-add" type="button"
                        onclick="openExpCategoryModal()" title="Add Category">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="branch_id" class="form-label">Select Branch <span class="text-danger">*</span></label>
            <div class="input-group exp-field-group">
                <select class="form-select" id="branch_id" name="branch_id" required>
                    <option value="">Select Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $expense->branch_id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @can('create_branches')
                    <button class="btn btn-outline-primary btn-exp-add" type="button"
                        onclick="openExpBranchModal()" title="Add Branch">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="account_id" class="form-label">Select Account <span class="text-danger">*</span></label>
            <div class="input-group exp-field-group">
                <select class="form-select" id="account_id" name="account_id" required>
                    <option value="">Select Account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" {{ $expense->account_id == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                @can('create_accounts')
                    <button class="btn btn-outline-primary btn-exp-add" type="button"
                        onclick="openExpAccountModal()" title="Add Account">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-12 mb-3">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                value="{{ old('amount', $expense->amount) }}" required placeholder="0.00">
        </div>

        <div class="col-12 mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" rows="3" placeholder="Expense details...">{{ old('note', $expense->note) }}</textarea>
        </div>

        <div class="col-3 mb-3">
            <label class="form-check-label" for="status">Is Active</label>
            <div class="form-check form-switch">
                <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="status"
                    name="status" style="width: 50px; height: 25px;" {{ $expense->status == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
        <button type="submit" class="btn btn-primary">Update Expense</button>
    </div>
</form>

@include('backend.expenses.ajax.partials.quick_add')
