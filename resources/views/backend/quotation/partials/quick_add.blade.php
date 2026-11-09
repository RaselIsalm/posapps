{{-- Quotation Create/Edit shared quick-add modals + AJAX (no toaster, no page reload) --}}

{{-- Add Customer Modal --}}
@can('create_customers')
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addCustomerForm" onsubmit="return saveQuoCustomer(event)">
                        @csrf
                        <div class="quo-form-error" id="quoCustomerFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Customer Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Phone *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Email (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Address (Optional)</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Note (Optional)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveCustomer">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Supplier Modal --}}
@can('create_suppliers')
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Supplier</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addSupplierForm" onsubmit="return saveQuoSupplier(event)">
                        @csrf
                        <div class="quo-form-error" id="quoSupplierFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Supplier name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="Company name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Phone *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="Phone number">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Address">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Note</label>
                            <input type="text" name="note" class="form-control" placeholder="Note">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Previous Due</label>
                            <input type="number" name="previous_due" class="form-control" step="any" min="0"
                                placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="quo_supplier_is_active"
                                    name="is_active" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="quo_supplier_is_active">Active</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSupplier">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Branch Modal --}}
@can('create_branches')
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addBranchForm" onsubmit="return saveQuoBranch(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="quo-form-error" id="quoBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="quoBranchManagerSelect" class="form-control" required>
                                        <option value="">Select Manager</option>
                                        @foreach ($branchManagers ?? [] as $manager)
                                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_users')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestManagerModal()" title="Add Manager">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Account *</label>
                                <div class="input-group nest-field-group">
                                    <select name="account_id" id="quoBranchAccountSelect" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($accounts ?? [] as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestAccountModal()" title="Add Account">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Address *</label>
                                <input type="text" name="address" class="form-control" required placeholder="Branch address">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone (Mobile)</label>
                                <input type="text" name="phone" class="form-control" placeholder="Mobile">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Telephone (Landline)</label>
                                <input type="text" name="telephone" class="form-control" placeholder="Landline">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">BIN No</label>
                                <input type="text" name="bin_number" class="form-control" placeholder="BIN number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Website</label>
                                <input type="text" name="website" class="form-control" placeholder="https://example.com">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Branch Logo</label>
                                <input type="file" name="branch_logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBranch">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Employee Modal --}}
@can('create_employees')
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Employee</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addEmployeeForm" onsubmit="return saveQuoEmployee(event)">
                        @csrf
                        <div class="quo-form-error" id="quoEmployeeFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Employee name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Email *</label>
                                <input type="email" name="email" class="form-control" required placeholder="Login email">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone *</label>
                                <input type="tel" name="phone" class="form-control" required placeholder="Phone number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Branch *</label>
                                <select name="branch_id" id="quoEmployeeBranch" class="form-control" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Department *</label>
                                <div class="input-group nest-field-group">
                                    <select name="department_id" id="quoEmployeeDepartmentSelect" class="form-control"
                                        required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments ?? [] as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_departments')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestDepartmentModal()" title="Add Department">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Role *</label>
                                <div class="input-group nest-field-group">
                                    <select name="role" id="quoEmployeeRoleSelect" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach ($employeeRoles ?? [] as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_roles')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestRoleModal()" title="Add Role">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Password *</label>
                                <input type="password" name="password" class="form-control" required minlength="6"
                                    autocomplete="new-password" placeholder="Login password">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Confirm Password *</label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    minlength="6" autocomplete="new-password" placeholder="Confirm password">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Salary *</label>
                                <input type="number" name="salary" class="form-control" required step="any" placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Max Commission</label>
                                <input type="number" name="max_commission" class="form-control" step="any" placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Sale Target</label>
                                <input type="number" name="sale_target" class="form-control" step="any" placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Joining Date *</label>
                                <input type="date" name="joining_date" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Leaving Date</label>
                                <input type="date" name="leaving_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_employee_status"
                                        name="status" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_employee_status">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveEmployee">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Account / Payment Method Modal --}}
@can('create_accounts')
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addAccountForm" onsubmit="return saveQuoAccount(event)">
                        @csrf
                        <div class="quo-form-error" id="quoAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Account name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Type *</label>
                                <select name="type" id="quo_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">
                                    Account Number <span id="quo_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="quo_account_number" class="form-control"
                                    placeholder="Not required for cash">
                                <small id="quo_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Initial Balance</label>
                                <input type="number" name="initial_balance" class="form-control" step="0.01" min="0"
                                    value="0" placeholder="0.00">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small fw-bold text-muted mb-1">Note</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_account_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_account_is_active">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_account_is_default"
                                        name="is_default" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_account_is_default">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="quo_account_manual"
                                        name="allow_manual_amount" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="quo_account_manual">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveAccount">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@include('backend.shared.partials.nested_branch_employee_quick_add', [
    'nx' => [
        'managerSelectId' => 'quoBranchManagerSelect',
        'accountSelectId' => 'quoBranchAccountSelect',
        'departmentSelectId' => 'quoEmployeeDepartmentSelect',
        'roleSelectId' => 'quoEmployeeRoleSelect',
        'branchParentModalId' => 'addBranchModal',
        'employeeParentModalId' => 'addEmployeeModal',
        'roles' => $employeeRoles ?? [],
    ],
])

@push('js')
    <script>
        // Hard rule: Quotation quick-add (+ Branch/Employee/Customer/Supplier/Account)
        // must NEVER show browser alert / toastr / floating toast.
        (function () {
            if (window.__quoAlertGuardInstalled) return;
            window.__quoAlertGuardInstalled = true;
            window.__quoQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__quoQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            const nativeConfirm = window.confirm;
            window.confirm = function () {
                if (window.__quoQuickAddSilent) return true;
                return nativeConfirm.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__quoQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        })();

        function quoMutePopups(on) {
            window.__quoQuickAddSilent = !!on;
            if (typeof toastr !== 'undefined') {
                if (on) {
                    if (!window.__quoToastrBackup) {
                        window.__quoToastrBackup = {
                            success: toastr.success,
                            error: toastr.error,
                            info: toastr.info,
                            warning: toastr.warning
                        };
                    }
                    toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
                } else if (window.__quoToastrBackup) {
                    toastr.success = window.__quoToastrBackup.success;
                    toastr.error = window.__quoToastrBackup.error;
                    toastr.info = window.__quoToastrBackup.info;
                    toastr.warning = window.__quoToastrBackup.warning;
                }
            }
        }

        function quoParseJson(response) {
            if (typeof response === 'string') {
                try {
                    return JSON.parse(response);
                } catch (err) {
                    return null;
                }
            }
            return response;
        }

        function quoEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function quoShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;

            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message && typeof message === 'object') {
                Object.keys(message).forEach(function (key) {
                    const items = message[key];
                    if (Array.isArray(items)) {
                        items.forEach(function (item) { messages.push(item); });
                    } else if (items) {
                        messages.push(items);
                    }
                });
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }

            if (!messages.length) {
                messages = [@json(__('Something went wrong. Please try again.'))];
            }

            let html = '<div class="quo-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + quoEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + quoEscapeHtml(m) + '</li>';
                });
                html += '</ul>';
            }

            box.innerHTML = html;
            box.classList.add('is-visible');

            if (formEl) {
                try {
                    formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (err) {}
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function quoClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.quo-field-error').remove();
            }
        }

        function quoMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block quo-field-error';
                feedback.textContent = msg;
                if (input.parentElement && input.parentElement.classList.contains('form-check')) {
                    input.parentElement.insertAdjacentElement('afterend', feedback);
                } else {
                    input.insertAdjacentElement('afterend', feedback);
                }
            });
        }

        function quoExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (err) {
                    payload = null;
                }
            }

            if (xhr.status === 419) {
                return {
                    messages: [@json(__('Your session has expired. Please refresh the page and try again.'))],
                    errors: null
                };
            }
            if (xhr.status === 403) {
                return {
                    messages: [(payload && payload.message) || @json(__('You do not have permission to perform this action.'))],
                    errors: null
                };
            }
            if (xhr.status === 422 && payload && payload.errors) {
                const messages = [];
                Object.keys(payload.errors).forEach(function (key) {
                    (payload.errors[key] || []).forEach(function (item) {
                        messages.push(item);
                    });
                });
                return {
                    messages: messages.length ? messages : [@json(__('Validation failed.'))],
                    errors: payload.errors
                };
            }
            if (payload && payload.message) {
                return { messages: [payload.message], errors: payload.errors || null };
            }
            if (xhr.status === 0) {
                return { messages: [@json(__('Network error. Please check your connection.'))], errors: null };
            }
            return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
        }

        function quoHandleSaveFail(errorId, formEl, xhr) {
            const payload = quoExtractErrorPayload(xhr);
            quoClearFormError(errorId, formEl);
            if (payload.errors) {
                quoMarkFieldErrors(formEl, payload.errors);
            }
            quoShowFormError(errorId, payload.messages, formEl);
        }

        function quoAppendSelectOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const $sel = $(el);
            const value = String(id);
            const label = text || ('#' + value);

            if ($sel.find('option[value="' + value + '"]').length === 0) {
                $sel.append(new Option(label, value, false, false));
            }

            if (selectIt) {
                $sel.find('option[disabled]').prop('selected', false);
                $sel.val(value).trigger('change');
            }
        }

        function quoAppendPlainOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const value = String(id);
            const label = text || ('#' + value);
            if ($(el).find('option[value="' + value + '"]').length === 0) {
                el.appendChild(new Option(label, value, false, false));
            }
            if (selectIt) {
                el.value = value;
            }
        }

        function quoPostJson(url, data, isFormData) {
            quoMutePopups(true);
            const options = {
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };
            if (isFormData) {
                options.processData = false;
                options.contentType = false;
            }
            return $.ajax(options).always(function () {
                quoMutePopups(false);
            });
        }

        function saveQuoCustomer(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addCustomerForm');
            const btn = $('#btnSaveCustomer');
            if (!form.length) return false;

            quoClearFormError('quoCustomerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('customers.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.customer) {
                        quoAppendSelectOption('customer_id', response.customer.id, response.customer.name, true);
                        $('#addCustomerModal').modal('hide');
                        form[0].reset();
                        quoClearFormError('quoCustomerFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoCustomerFormError', (response && response.message) || @json(__('Could not save customer.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoCustomerFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function saveQuoSupplier(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addSupplierForm');
            const btn = $('#btnSaveSupplier');
            if (!form.length) return false;

            quoClearFormError('quoSupplierFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('suppliers.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.supplier) {
                        const s = response.supplier;
                        if (s.is_active === false) {
                            quoShowFormError('quoSupplierFormError', @json(__('Supplier saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_supplier_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('supplier_id', s.id, s.name, true);
                        $('#addSupplierModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_supplier_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoSupplierFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoSupplierFormError', (response && response.message) || @json(__('Could not save supplier.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoSupplierFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function saveQuoBranch(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addBranchForm');
            const btn = $('#btnSaveBranch');
            if (!form.length) return false;

            quoClearFormError('quoBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('branches.store') }}", new FormData(form[0]), true)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            quoShowFormError('quoBranchFormError', @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_branch_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('branch_id', b.id, b.name, true);
                        quoAppendPlainOption('quoEmployeeBranch', b.id, b.name, false);
                        $('#addBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_branch_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoBranchFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoBranchFormError', (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoBranchFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function openQuoEmployeeModal() {
            const form = document.getElementById('addEmployeeForm');
            if (!form) return;
            form.reset();
            quoClearFormError('quoEmployeeFormError', form);
            const status = document.getElementById('quo_employee_status');
            if (status) status.checked = true;
            const joining = form.querySelector('[name="joining_date"]');
            if (joining && !joining.value) {
                joining.value = new Date().toISOString().slice(0, 10);
            }
            const currentBranch = document.getElementById('branch_id')?.value || '';
            const empBranch = document.getElementById('quoEmployeeBranch');
            if (empBranch && currentBranch) {
                empBranch.value = currentBranch;
            }
            $('#addEmployeeModal').modal('show');
        }

        function saveQuoEmployee(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addEmployeeForm');
            const btn = $('#btnSaveEmployee');
            if (!form.length) return false;

            quoClearFormError('quoEmployeeFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('employees.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.employee) {
                        const emp = response.employee;
                        if (!emp.status) {
                            quoShowFormError('quoEmployeeFormError', @json(__('Employee saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const status = document.getElementById('quo_employee_status');
                            if (status) status.checked = true;
                            return;
                        }
                        quoAppendSelectOption('employee_id', emp.id, emp.name, true);
                        const branchSel = document.getElementById('branch_id');
                        if (emp.branch_id && branchSel && (!branchSel.value || branchSel.selectedOptions[0]?.disabled)) {
                            $(branchSel).val(String(emp.branch_id)).trigger('change');
                        }
                        $('#addEmployeeModal').modal('hide');
                        form[0].reset();
                        const status = document.getElementById('quo_employee_status');
                        if (status) status.checked = true;
                        quoClearFormError('quoEmployeeFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoEmployeeFormError', (response && response.message) || @json(__('Could not save employee.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoEmployeeFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        function syncQuoAccountNumberRequirement() {
            const typeEl = document.getElementById('quo_account_type');
            const numberEl = document.getElementById('quo_account_number');
            const requiredMark = document.getElementById('quo_account_number_required');
            const hintEl = document.getElementById('quo_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        function openQuoAccountModal() {
            const form = document.getElementById('addAccountForm');
            if (!form) return;
            form.reset();
            quoClearFormError('quoAccountFormError', form);
            const active = document.getElementById('quo_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('quo_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncQuoAccountNumberRequirement();
            $('#addAccountModal').modal('show');
        }

        function saveQuoAccount(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addAccountForm');
            const btn = $('#btnSaveAccount');
            if (!form.length) return false;

            quoClearFormError('quoAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            quoPostJson("{{ route('accounts.store') }}", form.serialize(), false)
                .done(function(response) {
                    response = quoParseJson(response);
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            quoShowFormError('quoAccountFormError', @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('quo_account_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        quoAppendSelectOption('account_id', a.id, a.name, true);
                        quoAppendPlainOption('quoBranchAccountSelect', a.id, a.name, false);
                        $('#addAccountModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('quo_account_is_active');
                        if (active) active.checked = true;
                        quoClearFormError('quoAccountFormError', form[0]);
                        return;
                    }
                    quoShowFormError('quoAccountFormError', (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function(xhr) {
                    quoHandleSaveFail('quoAccountFormError', form[0], xhr);
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        }

        $(document).on('change', '#quo_account_type', syncQuoAccountNumberRequirement);
        $(document).on('input change', '#addCustomerForm input, #addCustomerForm select, #addCustomerForm textarea, #addSupplierForm input, #addSupplierForm select, #addSupplierForm textarea, #addBranchForm input, #addBranchForm select, #addBranchForm textarea, #addEmployeeForm input, #addEmployeeForm select, #addEmployeeForm textarea, #addAccountForm input, #addAccountForm select, #addAccountForm textarea', function () {
            $(this).removeClass('is-invalid');
            $(this).siblings('.quo-field-error').remove();
            $(this).closest('.form-check').next('.quo-field-error').remove();
        });
    </script>
@endpush
