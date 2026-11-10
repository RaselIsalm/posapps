{{-- Payroll Add/Edit: Employee + Payment Account quick-add (silent AJAX, teal header) --}}
<style>
    .pay-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .pay-field-group > .form-control,
    .pay-field-group > .form-select {
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
    }

    .pay-field-group .btn-pay-add {
        min-width: 42px;
        width: 42px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        align-self: stretch;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        margin-left: -1px;
        z-index: 1;
    }

    .pay-field-group:has(.btn-pay-add) > .form-control,
    .pay-field-group:has(.btn-pay-add) > .form-select {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .pay-form-error {
        display: none;
        border: 1px solid #f5c2c7;
        background: #f8d7da;
        color: #842029;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        font-size: 0.8125rem;
        line-height: 1.45;
    }

    .pay-form-error.is-visible {
        display: block;
    }

    .pay-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .pay-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .pay-field-group .btn-pay-add {
            min-width: 38px;
            width: 38px;
            flex-basis: 38px;
        }
    }
</style>

@can('create_employees')
<div class="modal fade" id="payAddEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Employee</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="payAddEmployeeForm" onsubmit="return savePayEmployee(event)" novalidate>
                    @csrf
                    <div class="pay-form-error" id="payEmployeeFormError" role="alert"></div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_name">Name *</label>
                            <input type="text" name="name" id="pay_emp_name" class="form-control" required placeholder="Employee name">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_email">Email *</label>
                            <input type="email" name="email" id="pay_emp_email" class="form-control" required placeholder="Login email">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_phone">Phone *</label>
                            <input type="tel" name="phone" id="pay_emp_phone" class="form-control" required placeholder="Phone number">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_branch">Branch *</label>
                            <div class="input-group nest-field-group">
                                <select name="branch_id" id="pay_emp_branch" class="form-control" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @can('create_branches')
                                    <button type="button" class="btn btn-outline-primary btn-nest-add"
                                        onclick="openPayBranchModal()" title="Add Branch">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_department">Department *</label>
                            <div class="input-group nest-field-group">
                                <select name="department_id" id="pay_emp_department" class="form-control" required>
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
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_role">Role *</label>
                            <div class="input-group nest-field-group">
                                <select name="role" id="pay_emp_role" class="form-control" required>
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
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_password">Password *</label>
                            <input type="password" name="password" id="pay_emp_password" class="form-control" required minlength="6"
                                autocomplete="new-password" placeholder="Login password">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_password_confirmation">Confirm Password *</label>
                            <input type="password" name="password_confirmation" id="pay_emp_password_confirmation" class="form-control"
                                required minlength="6" autocomplete="new-password" placeholder="Confirm password">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_salary">Salary *</label>
                            <input type="number" name="salary" id="pay_emp_salary" class="form-control" required step="any" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_max_commission">Max Commission</label>
                            <input type="number" name="max_commission" id="pay_emp_max_commission" class="form-control" step="any" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_sale_target">Sale Target</label>
                            <input type="number" name="sale_target" id="pay_emp_sale_target" class="form-control" step="any" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_joining_date">Joining Date *</label>
                            <input type="date" name="joining_date" id="pay_emp_joining_date" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_emp_leaving_date">Leaving Date</label>
                            <input type="date" name="leaving_date" id="pay_emp_leaving_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="pay_emp_status"
                                    name="status" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="pay_emp_status">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSavePayEmployee">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

@can('create_branches')
<div class="modal fade" id="payAddBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="payAddBranchForm" onsubmit="return savePayBranch(event)" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="pay-form-error" id="payBranchFormError" role="alert"></div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1">Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Branch name">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold text-muted mb-1">Manager *</label>
                            <div class="input-group nest-field-group">
                                <select name="manager_id" id="payBranchManagerSelect" class="form-control" required>
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
                                <select name="account_id" id="payBranchAccountSelect" class="form-control" required>
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
                                <input class="form-check-input" type="checkbox" role="switch" id="pay_branch_is_active"
                                    name="is_active" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="pay_branch_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSavePayBranch">
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
        'managerSelectId' => 'payBranchManagerSelect',
        'accountSelectId' => 'payBranchAccountSelect',
        'departmentSelectId' => 'pay_emp_department',
        'roleSelectId' => 'pay_emp_role',
        'branchParentModalId' => 'payAddBranchModal',
        'employeeParentModalId' => 'payAddEmployeeModal',
        'roles' => $employeeRoles ?? [],
    ],
])

@can('create_accounts')
<div class="modal fade" id="payAddAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="payAddAccountForm" onsubmit="return savePayAccount(event)" novalidate>
                    @csrf
                    <div class="pay-form-error" id="payAccountFormError" role="alert"></div>
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_account_name">Name *</label>
                            <input type="text" name="name" id="pay_account_name" class="form-control" required
                                placeholder="Account name" autocomplete="off">
                        </div>
                        <div class="col-12 col-sm-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_account_type">Type *</label>
                            <select name="type" id="pay_account_type" class="form-control" required>
                                <option value="cash" selected>Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile banking">Mobile Banking</option>
                                <option value="digital wallet">Digital Wallet</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_account_number">
                                Account Number <span id="pay_account_number_required" class="text-danger d-none">*</span>
                            </label>
                            <input type="text" name="account_number" id="pay_account_number" class="form-control"
                                placeholder="Not required for cash" autocomplete="off">
                            <small id="pay_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                        </div>
                        <div class="col-12 col-sm-6 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_account_initial_balance">Initial Balance</label>
                            <input type="number" name="initial_balance" id="pay_account_initial_balance" class="form-control"
                                step="0.01" min="0" max="9999999999999.99" value="0" placeholder="0.00">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="pay_account_note">Note</label>
                            <textarea name="note" id="pay_account_note" class="form-control" rows="2"
                                placeholder="Optional note"></textarea>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="pay_account_is_active"
                                    name="is_active" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="pay_account_is_active">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="pay_account_is_default"
                                    name="is_default" style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="pay_account_is_default">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="pay_account_manual"
                                    name="allow_manual_amount" style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="pay_account_manual">Yes</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSavePayAccount">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<script>
(function () {
    const payParentModalId = 'payrollModal';
    let payPausedParentModalId = null;
    let payResumeScheduled = false;
    let paySuppressEmpResume = false;
    let payPausedEmployeeForBranch = false;
    const currencySymbol = @json(\App\Helpers\CurrencyHelper::getSymbol());

    ['payAddEmployeeModal', 'payAddAccountModal', 'payAddBranchModal'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
    });

    if (!window.__payAlertGuardInstalled) {
        window.__payAlertGuardInstalled = true;
        window.__payQuickAddSilent = false;

        const nativeAlert = window.alert;
        window.alert = function () {
            if (window.__payQuickAddSilent) return;
            return nativeAlert.apply(window, arguments);
        };

        if (typeof window.showAppToast === 'function') {
            const nativeToast = window.showAppToast;
            window.showAppToast = function () {
                if (window.__payQuickAddSilent) return;
                return nativeToast.apply(window, arguments);
            };
        }

        if (typeof window.showFloatingAlert === 'function') {
            const nativeFloating = window.showFloatingAlert;
            window.showFloatingAlert = function () {
                if (window.__payQuickAddSilent) return;
                return nativeFloating.apply(window, arguments);
            };
        }
    }

    function payMutePopups(on) {
        window.__payQuickAddSilent = !!on;
        if (typeof toastr === 'undefined') return;
        if (on) {
            if (!window.__payToastrBackup) {
                window.__payToastrBackup = {
                    success: toastr.success,
                    error: toastr.error,
                    info: toastr.info,
                    warning: toastr.warning
                };
            }
            toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
        } else if (window.__payToastrBackup) {
            toastr.success = window.__payToastrBackup.success;
            toastr.error = window.__payToastrBackup.error;
            toastr.info = window.__payToastrBackup.info;
            toastr.warning = window.__payToastrBackup.warning;
        }
    }

    function payGetBsModal(el) {
        if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
        return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
    }

    function payHideModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const bs = payGetBsModal(el);
        if (bs) bs.hide();
        else $('#' + id).modal('hide');
    }

    function payShowModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const bs = payGetBsModal(el);
        if (bs) bs.show();
        else $('#' + id).modal('show');
    }

    function payClearStrayBackdrops() {
        document.querySelectorAll('.modal-backdrop').forEach(function (b) {
            b.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
    }

    function payResumeParentModal() {
        if (!payPausedParentModalId || payResumeScheduled) return;
        const parentId = payPausedParentModalId;
        payPausedParentModalId = null;
        payResumeScheduled = true;
        setTimeout(function () {
            payResumeScheduled = false;
            payClearStrayBackdrops();
            payShowModal(parentId);
        }, 200);
    }

    function payOpenQuickOverParent(quickModalId) {
        const parentEl = document.getElementById(payParentModalId);
        const openQuick = function () {
            payShowModal(quickModalId);
        };
        const parentVisible = parentEl && (
            parentEl.classList.contains('show') ||
            $(parentEl).is(':visible')
        );

        if (parentVisible) {
            payPausedParentModalId = payParentModalId;
            payResumeScheduled = false;
            const onParentHidden = function () {
                parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                openQuick();
            };
            parentEl.addEventListener('hidden.bs.modal', onParentHidden);
            payHideModal(payParentModalId);
        } else {
            payPausedParentModalId = null;
            openQuick();
        }
    }

    function payOpenBranchOverEmployee() {
        const empEl = document.getElementById('payAddEmployeeModal');
        let opened = false;
        const openBranch = function () {
            if (opened) return;
            opened = true;
            payShowModal('payAddBranchModal');
        };
        const empVisible = empEl && (
            empEl.classList.contains('show') ||
            (typeof $ !== 'undefined' && $(empEl).is(':visible'))
        );
        if (empVisible) {
            paySuppressEmpResume = true;
            payPausedEmployeeForBranch = true;
            const onEmpHidden = function () {
                empEl.removeEventListener('hidden.bs.modal', onEmpHidden);
                openBranch();
            };
            empEl.addEventListener('hidden.bs.modal', onEmpHidden);
            payHideModal('payAddEmployeeModal');
            setTimeout(function () {
                empEl.removeEventListener('hidden.bs.modal', onEmpHidden);
                openBranch();
            }, 450);
        } else {
            payPausedEmployeeForBranch = false;
            openBranch();
        }
    }

    function payResumeEmployeeAfterBranch() {
        setTimeout(function () {
            if (!payPausedEmployeeForBranch) return;
            const nestOpen = document.querySelector('#nxNestManagerModal.show, #nxNestAccountModal.show, #nxNestDepartmentModal.show, #nxNestRoleModal.show');
            if (nestOpen) return;
            const branchEl = document.getElementById('payAddBranchModal');
            if (branchEl && branchEl.classList.contains('show')) return;
            payPausedEmployeeForBranch = false;
            payClearStrayBackdrops();
            payShowModal('payAddEmployeeModal');
            paySuppressEmpResume = false;
        }, 350);
    }

    const empModal = document.getElementById('payAddEmployeeModal');
    if (empModal && !empModal.dataset.payParentResumeBound) {
        empModal.dataset.payParentResumeBound = '1';
        empModal.addEventListener('hidden.bs.modal', function () {
            if (paySuppressEmpResume) return;
            payResumeParentModal();
        });
    }

    const branchModal = document.getElementById('payAddBranchModal');
    if (branchModal && !branchModal.dataset.payEmpResumeBound) {
        branchModal.dataset.payEmpResumeBound = '1';
        branchModal.addEventListener('hidden.bs.modal', function () {
            payResumeEmployeeAfterBranch();
        });
    }

    const accModal = document.getElementById('payAddAccountModal');
    if (accModal && !accModal.dataset.payParentResumeBound) {
        accModal.dataset.payParentResumeBound = '1';
        accModal.addEventListener('hidden.bs.modal', function () {
            payResumeParentModal();
        });
    }

    function payEscapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function payShowFormError(errorId, message, formEl) {
        const box = document.getElementById(errorId);
        if (!box) return;
        let messages = [];
        if (Array.isArray(message)) {
            messages = message.filter(Boolean);
        } else if (message) {
            messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
        }
        if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

        let html = '<div class="pay-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
        if (messages.length === 1) {
            html += '<div>' + payEscapeHtml(messages[0]) + '</div>';
        } else {
            html += '<ul>';
            messages.forEach(function (m) { html += '<li>' + payEscapeHtml(m) + '</li>'; });
            html += '</ul>';
        }
        box.innerHTML = html;
        box.classList.add('is-visible');
        if (formEl) {
            try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
        }
    }

    function payClearFormError(errorId, formEl) {
        const box = document.getElementById(errorId);
        if (box) {
            box.classList.remove('is-visible');
            box.innerHTML = '';
        }
        if (formEl) {
            $(formEl).find('.is-invalid').removeClass('is-invalid');
            $(formEl).find('.pay-field-error').remove();
        }
    }

    function payMarkFieldErrors(formEl, errors) {
        if (!formEl || !errors) return;
        Object.keys(errors).forEach(function (field) {
            const input = formEl.querySelector('[name="' + field + '"]');
            if (input) input.classList.add('is-invalid');
        });
    }

    function payExtractErrorPayload(xhr) {
        let payload = xhr.responseJSON || null;
        if (!payload && xhr.responseText) {
            try { payload = JSON.parse(xhr.responseText); } catch (e) { payload = null; }
        }
        if (xhr.status === 419) {
            return { messages: [@json(__('Your session has expired. Please refresh the page and try again.'))], errors: null };
        }
        if (xhr.status === 403) {
            return { messages: [(payload && payload.message) || @json(__('You do not have permission to perform this action.'))], errors: null };
        }
        if (xhr.status === 422 && payload && payload.errors) {
            const messages = [];
            Object.keys(payload.errors).forEach(function (key) {
                (payload.errors[key] || []).forEach(function (item) { messages.push(item); });
            });
            return { messages: messages.length ? messages : [@json(__('Validation failed.'))], errors: payload.errors };
        }
        if (payload && payload.message) {
            return { messages: [payload.message], errors: payload.errors || null };
        }
        if (xhr.status === 0) {
            return { messages: [@json(__('Network error. Please check your connection.'))], errors: null };
        }
        return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
    }

    function payHandleSaveFail(errorId, formEl, xhr) {
        const payload = payExtractErrorPayload(xhr);
        payClearFormError(errorId, formEl);
        if (payload.errors) payMarkFieldErrors(formEl, payload.errors);
        payShowFormError(errorId, payload.messages, formEl);
    }

    function payFormatMoney(amount) {
        const n = parseFloat(amount);
        const val = isNaN(n) ? 0 : n;
        return currencySymbol + val.toFixed(2);
    }

    function payAppendOption(selectId, id, text, selectIt) {
        const el = document.getElementById(selectId);
        if (!el || id == null) return;
        const value = String(id);
        const label = text || ('#' + value);
        let exists = false;
        for (let i = 0; i < el.options.length; i++) {
            if (el.options[i].value === value) {
                exists = true;
                el.options[i].text = label;
                break;
            }
        }
        if (!exists) {
            el.appendChild(new Option(label, value, false, false));
        }
        if (selectIt) {
            el.value = value;
            $(el).trigger('change');
        }
    }

    function payAppendEmployeeOption(id, name, phone, selectIt) {
        const el = document.querySelector('#payrollModal select[name="employee_id"]');
        if (!el || id == null) return;
        const value = String(id);
        const label = name + (phone ? ' (' + phone + ')' : '');
        let opt = el.querySelector('option[value="' + value.replace(/"/g, '\\"') + '"]');
        if (!opt) {
            opt = new Option(label, value, false, false);
            el.appendChild(opt);
        } else {
            opt.text = label;
        }
        if (selectIt) {
            el.value = value;
            $(el).trigger('change');
        }
    }

    function payAppendAccountOption(id, name, balance, selectIt) {
        const el = document.getElementById('payment_method');
        if (!el || id == null) return;
        const value = String(id);
        const bal = parseFloat(balance);
        const balVal = isNaN(bal) ? 0 : bal;
        const label = name + ' (' + payFormatMoney(balVal) + ')';
        let opt = el.querySelector('option[value="' + value.replace(/"/g, '\\"') + '"]');
        if (!opt) {
            opt = new Option(label, value, false, false);
            el.appendChild(opt);
        } else {
            opt.text = label;
        }
        opt.setAttribute('data-balance', String(balVal));
        if (selectIt) {
            el.value = value;
            $(el).trigger('change');
        }
    }

    function payPostJson(url, data) {
        return $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
    }

    function payParseJson(response) {
        if (typeof response === 'string') {
            try { return JSON.parse(response); } catch (e) { return null; }
        }
        return response;
    }

    function syncPayAccountNumberRequirement() {
        const typeEl = document.getElementById('pay_account_type');
        const numberEl = document.getElementById('pay_account_number');
        const requiredMark = document.getElementById('pay_account_number_required');
        const hintEl = document.getElementById('pay_account_number_hint');
        if (!typeEl || !numberEl) return;
        const isCash = typeEl.value === 'cash';
        numberEl.required = !isCash;
        numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
        if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
        if (hintEl) hintEl.classList.toggle('d-none', !isCash);
    }

    window.openPayEmployeeModal = function () {
        const form = document.getElementById('payAddEmployeeForm');
        if (!form) return;
        form.reset();
        payClearFormError('payEmployeeFormError', form);
        const status = document.getElementById('pay_emp_status');
        if (status) status.checked = true;
        const join = document.getElementById('pay_emp_joining_date');
        if (join) join.value = @json(date('Y-m-d'));
        payOpenQuickOverParent('payAddEmployeeModal');
        setTimeout(function () {
            const nameEl = document.getElementById('pay_emp_name');
            if (nameEl) nameEl.focus();
        }, 350);
    };

    window.openPayBranchModal = function () {
        const form = document.getElementById('payAddBranchForm');
        if (!form) return;
        form.reset();
        payClearFormError('payBranchFormError', form);
        const active = document.getElementById('pay_branch_is_active');
        if (active) active.checked = true;
        payOpenBranchOverEmployee();
        setTimeout(function () {
            const nameEl = form.querySelector('[name="name"]');
            if (nameEl) nameEl.focus();
        }, 350);
    };

    window.openPayAccountModal = function () {
        const form = document.getElementById('payAddAccountForm');
        if (!form) return;
        form.reset();
        payClearFormError('payAccountFormError', form);
        const active = document.getElementById('pay_account_is_active');
        if (active) active.checked = true;
        const typeEl = document.getElementById('pay_account_type');
        if (typeEl) typeEl.value = 'cash';
        const bal = document.getElementById('pay_account_initial_balance');
        if (bal) bal.value = '0';
        syncPayAccountNumberRequirement();
        payOpenQuickOverParent('payAddAccountModal');
        setTimeout(function () {
            const nameEl = document.getElementById('pay_account_name');
            if (nameEl) nameEl.focus();
        }, 350);
    };

    window.savePayBranch = function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#payAddBranchForm');
        const btn = $('#btnSavePayBranch');
        if (!form.length) return false;

        payClearFormError('payBranchFormError', form[0]);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        payMutePopups(true);

        $.ajax({
            url: "{{ route('branches.store') }}",
            type: 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .done(function (response) {
                response = payParseJson(response);
                if (response && response.success && response.branch) {
                    const b = response.branch;
                    if (!b.status) {
                        payShowFormError('payBranchFormError',
                            @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                        return;
                    }
                    payAppendOption('pay_emp_branch', b.id, b.name, true);
                    payHideModal('payAddBranchModal');
                    form[0].reset();
                    const active = document.getElementById('pay_branch_is_active');
                    if (active) active.checked = true;
                    return;
                }
                payShowFormError('payBranchFormError',
                    (response && response.message) || @json(__('Could not save branch.')), form[0]);
            })
            .fail(function (xhr) {
                payHandleSaveFail('payBranchFormError', form[0], xhr);
            })
            .always(function () {
                payMutePopups(false);
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
            });

        return false;
    };

    window.savePayEmployee = function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#payAddEmployeeForm');
        const btn = $('#btnSavePayEmployee');
        if (!form.length) return false;

        payClearFormError('payEmployeeFormError', form[0]);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        payMutePopups(true);

        const phone = String($('#pay_emp_phone').val() || '').trim();
        const name = String($('#pay_emp_name').val() || '').trim();

        payPostJson("{{ route('employees.store') }}", form.serialize())
            .done(function (response) {
                if (response && response.success && response.employee) {
                    const emp = response.employee;
                    if (!emp.status) {
                        payShowFormError('payEmployeeFormError',
                            @json(__('Employee saved, but it is inactive so it was not selected.')), form[0]);
                        return;
                    }
                    payAppendEmployeeOption(emp.id, emp.name || name, phone, true);
                    payHideModal('payAddEmployeeModal');
                    form[0].reset();
                    const status = document.getElementById('pay_emp_status');
                    if (status) status.checked = true;
                    return;
                }
                payShowFormError('payEmployeeFormError',
                    (response && response.message) || @json(__('Could not save employee.')), form[0]);
            })
            .fail(function (xhr) {
                payHandleSaveFail('payEmployeeFormError', form[0], xhr);
            })
            .always(function () {
                payMutePopups(false);
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
            });

        return false;
    };

    window.savePayAccount = function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#payAddAccountForm');
        const btn = $('#btnSavePayAccount');
        if (!form.length) return false;

        payClearFormError('payAccountFormError', form[0]);
        syncPayAccountNumberRequirement();

        const typeEl = document.getElementById('pay_account_type');
        const numberEl = document.getElementById('pay_account_number');
        if (typeEl && numberEl && typeEl.value !== 'cash' && !String(numberEl.value || '').trim()) {
            payMarkFieldErrors(form[0], { account_number: [@json(__('Account number is required for this type.'))] });
            payShowFormError('payAccountFormError', @json(__('Account number is required for this type.')), form[0]);
            return false;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        payMutePopups(true);

        const balance = parseFloat($('#pay_account_initial_balance').val()) || 0;
        const name = String($('#pay_account_name').val() || '').trim();

        payPostJson("{{ route('accounts.store') }}", form.serialize())
            .done(function (response) {
                if (response && response.success && response.account) {
                    const a = response.account;
                    if (!a.is_active) {
                        payShowFormError('payAccountFormError',
                            @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                        return;
                    }
                    payAppendAccountOption(a.id, a.name || name, balance, true);
                    payAppendOption('payBranchAccountSelect', a.id, a.name || name, false);
                    payHideModal('payAddAccountModal');
                    form[0].reset();
                    const active = document.getElementById('pay_account_is_active');
                    if (active) active.checked = true;
                    return;
                }
                payShowFormError('payAccountFormError',
                    (response && response.message) || @json(__('Could not save account.')), form[0]);
            })
            .fail(function (xhr) {
                payHandleSaveFail('payAccountFormError', form[0], xhr);
            })
            .always(function () {
                payMutePopups(false);
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
            });

        return false;
    };

    $(document).off('change.payAccType', '#pay_account_type')
        .on('change.payAccType', '#pay_account_type', syncPayAccountNumberRequirement);
})();
</script>
