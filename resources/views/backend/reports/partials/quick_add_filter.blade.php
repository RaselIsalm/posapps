{{-- Report / list filter silent quick-add (supplier, customer, branch, category). Courier: page wires openDelCourierModal separately. --}}
@once('rpt-quick-add-filter')
@php
    $rpt = array_merge([
        'supplier' => false,
        'customer' => false,
        'branch' => false,
        'category' => false,
        'courier' => false,
    ], $rpt ?? []);

    $rptSelectIds = array_merge([
        'supplier' => 'supplier_id',
        'customer' => 'customer_id',
        'branch' => 'branch_id',
        'category' => 'category_id',
        'courier' => 'filter_courier',
    ], $rptSelectIds ?? []);
@endphp

@push('css')
<style>
    .rpt-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }
    .rpt-field-group > .form-control,
    .rpt-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .rpt-field-group .select2-container {
        flex: 1 1 auto;
        width: 1% !important;
    }
    .rpt-field-group .select2-container .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .rpt-field-group:has(.btn-rpt-add) .select2-container .select2-selection--single,
    .rpt-field-group:has(.btn-rpt-add) > .form-select,
    .rpt-field-group:has(.btn-rpt-add) > .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .rpt-field-group .btn-rpt-add {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 42px;
        height: auto;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 0;
    }
    .rpt-form-error {
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
    .rpt-form-error.is-visible { display: block; }
    .rpt-form-error-title { font-weight: 700; margin-bottom: 4px; }
    .rpt-form-error ul { margin: 0; padding-left: 1.1rem; }
    .rpt-field-error { font-size: 0.75rem; }
    @media (max-width: 575.98px) {
        .rpt-field-group .btn-rpt-add { min-width: 38px; }
    }
</style>
@endpush

@if (!empty($rpt['supplier']))
@can('create_suppliers')
<div class="modal fade" id="rptAddSupplierModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Supplier</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="rptAddSupplierForm" onsubmit="return saveRptSupplier(event)">
                    @csrf
                    <div class="rpt-form-error" id="rptSupplierFormError" role="alert"></div>
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
                        <input type="number" name="previous_due" class="form-control" step="any" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="rpt_supplier_is_active"
                                name="is_active" checked style="width: 40px; height: 20px;">
                            <label class="form-check-label ms-2" for="rpt_supplier_is_active">Active</label>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveRptSupplier">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endif

@if (!empty($rpt['customer']))
@can('create_customers')
<div class="modal fade" id="rptAddCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="rptAddCustomerForm" onsubmit="return saveRptCustomer(event)">
                    @csrf
                    <div class="rpt-form-error" id="rptCustomerFormError" role="alert"></div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Phone *</label>
                        <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Previous Due</label>
                        <input type="number" name="previous_due" class="form-control" step="any" min="0"
                            placeholder="0.00" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="rpt_customer_status"
                                name="status" checked style="width: 40px; height: 20px;">
                            <label class="form-check-label ms-2" for="rpt_customer_status">Active</label>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveRptCustomer">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endif

@if (!empty($rpt['branch']))
@can('create_branches')
<div class="modal fade" id="rptAddBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="rptAddBranchForm" onsubmit="return saveRptBranch(event)" enctype="multipart/form-data">
                    @csrf
                    <div class="rpt-form-error" id="rptBranchFormError" role="alert"></div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Branch name">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Manager *</label>
                        <div class="input-group nest-field-group">
                            <select name="manager_id" id="rpt_manager_id" class="form-control" required>
                                <option value="">Select Manager</option>
                                @foreach ($employees ?? [] as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
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
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Account *</label>
                        <div class="input-group nest-field-group">
                            <select name="account_id" id="rpt_account_id" class="form-control" required>
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
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Address *</label>
                        <input type="text" name="address" class="form-control" required placeholder="Address">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Branch Logo</label>
                        <input type="file" name="branch_logo" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="rpt_branch_is_active"
                                name="is_active" checked style="width: 40px; height: 20px;">
                            <label class="form-check-label ms-2" for="rpt_branch_is_active">Active</label>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveRptBranch">
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
        'managerSelectId' => 'rpt_manager_id',
        'accountSelectId' => 'rpt_account_id',
        'departmentSelectId' => 'rptUnusedDept',
        'roleSelectId' => 'rptUnusedRole',
        'branchParentModalId' => 'rptAddBranchModal',
        'employeeParentModalId' => 'rptAddBranchModal',
        'roles' => $employeeRoles ?? $roles ?? [],
    ],
])
@endif

@if (!empty($rpt['category']))
@can('create_categories')
<div class="modal fade" id="rptAddCategoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Add Category</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="rptAddCategoryForm" enctype="multipart/form-data" onsubmit="return saveRptCategory(event)">
                    @csrf
                    <div class="rpt-form-error" id="rptCategoryFormError" role="alert"></div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Category Name">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Parent Category</label>
                        <select name="parent_id" id="rpt_category_parent" class="form-control">
                            <option value="">Select Parent</option>
                            @foreach ($categories ?? [] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="rpt_category_is_active"
                                name="is_active" checked style="width: 40px; height: 20px;">
                            <label class="form-check-label ms-2" for="rpt_category_is_active">Active</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted mb-1">Image</label>
                        <input type="file" name="image" id="rpt_category_image" class="form-control" accept=".jpg,.jpeg,.png"
                            onchange="document.getElementById('rpt_category_image_preview').src = window.URL.createObjectURL(this.files[0])">
                    </div>
                    <div class="mb-3 text-center">
                        <img id="rpt_category_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                            class="img-fluid border rounded mt-2" alt="Image Preview" style="max-height: 80px; max-width: 80px;" />
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveRptCategory">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endif

{{-- Courier: do not duplicate modal here. Delivery list should call openDelCourierModal() (partial already included on that page). --}}

@push('js')
<script>
(function () {
    if (window.__rptAlertGuardInstalled) return;
    window.__rptAlertGuardInstalled = true;
    window.__rptQuickAddSilent = false;

    const nativeAlert = window.alert;
    window.alert = function () {
        if (window.__rptQuickAddSilent) return;
        return nativeAlert.apply(window, arguments);
    };

    if (typeof window.showAppToast === 'function') {
        const nativeToast = window.showAppToast;
        window.showAppToast = function () {
            if (window.__rptQuickAddSilent) return;
            return nativeToast.apply(window, arguments);
        };
    }
})();

window.rptSelectIds = window.rptSelectIds || @json($rptSelectIds);

function rptMutePopups(on) {
    window.__rptQuickAddSilent = !!on;
    if (typeof toastr === 'undefined') return;
    if (on) {
        if (!window.__rptToastrBackup) {
            window.__rptToastrBackup = {
                success: toastr.success,
                error: toastr.error,
                info: toastr.info,
                warning: toastr.warning
            };
        }
        toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
    } else if (window.__rptToastrBackup) {
        toastr.success = window.__rptToastrBackup.success;
        toastr.error = window.__rptToastrBackup.error;
        toastr.info = window.__rptToastrBackup.info;
        toastr.warning = window.__rptToastrBackup.warning;
    }
}

function rptParseJson(response) {
    if (typeof response === 'string') {
        try { return JSON.parse(response); } catch (err) { return null; }
    }
    return response;
}

function rptEscapeHtml(text) {
    return String(text == null ? '' : text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function rptShowFormError(errorId, message, formEl) {
    const box = document.getElementById(errorId);
    if (!box) return;
    let messages = [];
    if (Array.isArray(message)) {
        messages = message.filter(Boolean);
    } else if (message && typeof message === 'object') {
        Object.keys(message).forEach(function (key) {
            const items = message[key];
            if (Array.isArray(items)) items.forEach(function (item) { messages.push(item); });
            else if (items) messages.push(items);
        });
    } else if (message) {
        messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
    }
    if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

    let html = '<div class="rpt-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
    if (messages.length === 1) {
        html += '<div>' + rptEscapeHtml(messages[0]) + '</div>';
    } else {
        html += '<ul>';
        messages.forEach(function (m) { html += '<li>' + rptEscapeHtml(m) + '</li>'; });
        html += '</ul>';
    }
    box.innerHTML = html;
    box.classList.add('is-visible');
    if (formEl) {
        try { formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
    }
}

function rptClearFormError(errorId, formEl) {
    const box = document.getElementById(errorId);
    if (box) {
        box.classList.remove('is-visible');
        box.innerHTML = '';
    }
    if (formEl) {
        $(formEl).find('.is-invalid').removeClass('is-invalid');
        $(formEl).find('.rpt-field-error').remove();
    }
}

function rptMarkFieldErrors(formEl, errors) {
    if (!formEl || !errors) return;
    Object.keys(errors).forEach(function (field) {
        const input = formEl.querySelector('[name="' + field + '"]');
        if (!input) return;
        input.classList.add('is-invalid');
        const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
        if (!msg) return;
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block rpt-field-error';
        feedback.textContent = msg;
        if (input.parentElement && input.parentElement.classList.contains('form-check')) {
            input.parentElement.insertAdjacentElement('afterend', feedback);
        } else {
            input.insertAdjacentElement('afterend', feedback);
        }
    });
}

function rptExtractError(xhr) {
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

function rptHandleSaveFail(errorId, formEl, xhr) {
    const payload = rptExtractError(xhr);
    rptClearFormError(errorId, formEl);
    if (payload.errors) rptMarkFieldErrors(formEl, payload.errors);
    rptShowFormError(errorId, payload.messages, formEl);
}

function rptAppendSelectOption(selectId, id, text, selectIt) {
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

function rptPostJson(url, data, isFormData) {
    rptMutePopups(true);
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
        rptMutePopups(false);
    });
}

function rptPhoneLabel(name, phone) {
    const n = name || '';
    const p = (phone || '').toString().trim();
    return p ? (n + ' (' + p + ')') : n;
}

window.openRptSupplierModal = function () {
    const form = document.getElementById('rptAddSupplierForm');
    if (!form) return;
    form.reset();
    rptClearFormError('rptSupplierFormError', form);
    const active = document.getElementById('rpt_supplier_is_active');
    if (active) active.checked = true;
    $('#rptAddSupplierModal').modal('show');
    setTimeout(function () {
        const el = form.querySelector('[name="name"]');
        if (el) el.focus();
    }, 300);
};

window.openRptCustomerModal = function () {
    const form = document.getElementById('rptAddCustomerForm');
    if (!form) return;
    form.reset();
    rptClearFormError('rptCustomerFormError', form);
    const status = document.getElementById('rpt_customer_status');
    if (status) status.checked = true;
    const due = form.querySelector('[name="previous_due"]');
    if (due) due.value = '0';
    $('#rptAddCustomerModal').modal('show');
    setTimeout(function () {
        const el = form.querySelector('[name="phone"]');
        if (el) el.focus();
    }, 300);
};

window.openRptBranchModal = function () {
    const form = document.getElementById('rptAddBranchForm');
    if (!form) return;
    form.reset();
    rptClearFormError('rptBranchFormError', form);
    const active = document.getElementById('rpt_branch_is_active');
    if (active) active.checked = true;
    $('#rptAddBranchModal').modal('show');
    setTimeout(function () {
        const el = form.querySelector('[name="name"]');
        if (el) el.focus();
    }, 300);
};

window.openRptCategoryModal = function () {
    const form = document.getElementById('rptAddCategoryForm');
    if (!form) return;
    form.reset();
    rptClearFormError('rptCategoryFormError', form);
    const active = document.getElementById('rpt_category_is_active');
    if (active) active.checked = true;
    const preview = document.getElementById('rpt_category_image_preview');
    if (preview) preview.src = '{{ url('backend/assets/images/preview_image.png') }}';
    $('#rptAddCategoryModal').modal('show');
    setTimeout(function () {
        const el = form.querySelector('[name="name"]');
        if (el) el.focus();
    }, 300);
};

function saveRptSupplier(e) {
    e.preventDefault();
    e.stopPropagation();
    const form = $('#rptAddSupplierForm');
    const btn = $('#btnSaveRptSupplier');
    if (!form.length) return false;

    rptClearFormError('rptSupplierFormError', form[0]);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    const phoneVal = form.find('[name="phone"]').val();

    rptPostJson("{{ route('suppliers.store') }}", form.serialize(), false)
        .done(function (response) {
            response = rptParseJson(response);
            if (response && response.success && response.supplier) {
                const s = response.supplier;
                if (s.is_active === false || s.is_active === 0) {
                    rptShowFormError('rptSupplierFormError', @json(__('Supplier saved, but it is inactive so it was not selected.')), form[0]);
                    form[0].reset();
                    const active = document.getElementById('rpt_supplier_is_active');
                    if (active) active.checked = true;
                    return;
                }
                const label = rptPhoneLabel(s.name, s.phone || phoneVal);
                rptAppendSelectOption(window.rptSelectIds.supplier, s.id, label, true);
                $('#rptAddSupplierModal').modal('hide');
                form[0].reset();
                const active = document.getElementById('rpt_supplier_is_active');
                if (active) active.checked = true;
                rptClearFormError('rptSupplierFormError', form[0]);
                return;
            }
            rptShowFormError('rptSupplierFormError', (response && response.message) || @json(__('Could not save supplier.')), form[0]);
        })
        .fail(function (xhr) {
            rptHandleSaveFail('rptSupplierFormError', form[0], xhr);
        })
        .always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        });

    return false;
}

function saveRptCustomer(e) {
    e.preventDefault();
    e.stopPropagation();
    const form = $('#rptAddCustomerForm');
    const btn = $('#btnSaveRptCustomer');
    if (!form.length) return false;

    rptClearFormError('rptCustomerFormError', form[0]);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    rptPostJson("{{ route('customers.store') }}", form.serialize(), false)
        .done(function (response) {
            response = rptParseJson(response);
            if (response && response.success && response.customer) {
                const c = response.customer;
                if (c.status === false || c.status === 0) {
                    rptShowFormError('rptCustomerFormError', @json(__('Customer saved, but it is inactive so it was not selected.')), form[0]);
                    form[0].reset();
                    const status = document.getElementById('rpt_customer_status');
                    if (status) status.checked = true;
                    return;
                }
                const label = rptPhoneLabel(c.name, c.phone);
                rptAppendSelectOption(window.rptSelectIds.customer, c.id, label, true);
                $('#rptAddCustomerModal').modal('hide');
                form[0].reset();
                const status = document.getElementById('rpt_customer_status');
                if (status) status.checked = true;
                rptClearFormError('rptCustomerFormError', form[0]);
                return;
            }
            rptShowFormError('rptCustomerFormError', (response && response.message) || @json(__('Could not save customer.')), form[0]);
        })
        .fail(function (xhr) {
            rptHandleSaveFail('rptCustomerFormError', form[0], xhr);
        })
        .always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        });

    return false;
}

function saveRptBranch(e) {
    e.preventDefault();
    e.stopPropagation();
    const form = $('#rptAddBranchForm');
    const btn = $('#btnSaveRptBranch');
    if (!form.length) return false;

    rptClearFormError('rptBranchFormError', form[0]);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    const formData = new FormData(form[0]);
    if (!$('#rpt_branch_is_active').is(':checked')) {
        formData.delete('is_active');
    }

    rptPostJson("{{ route('branches.store') }}", formData, true)
        .done(function (response) {
            response = rptParseJson(response);
            if (response && response.success && response.branch) {
                const b = response.branch;
                if (b.status === false || b.status === 0) {
                    rptShowFormError('rptBranchFormError', @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                    form[0].reset();
                    const active = document.getElementById('rpt_branch_is_active');
                    if (active) active.checked = true;
                    return;
                }
                rptAppendSelectOption(window.rptSelectIds.branch, b.id, b.name, true);
                $('#rptAddBranchModal').modal('hide');
                form[0].reset();
                const active = document.getElementById('rpt_branch_is_active');
                if (active) active.checked = true;
                rptClearFormError('rptBranchFormError', form[0]);
                return;
            }
            rptShowFormError('rptBranchFormError', (response && response.message) || @json(__('Could not save branch.')), form[0]);
        })
        .fail(function (xhr) {
            rptHandleSaveFail('rptBranchFormError', form[0], xhr);
        })
        .always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        });

    return false;
}

function saveRptCategory(e) {
    e.preventDefault();
    e.stopPropagation();
    const form = $('#rptAddCategoryForm');
    const btn = $('#btnSaveRptCategory');
    if (!form.length) return false;

    rptClearFormError('rptCategoryFormError', form[0]);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    const formData = new FormData(form[0]);
    if (!$('#rpt_category_is_active').is(':checked')) {
        formData.delete('is_active');
    }

    rptPostJson("{{ route('categories.store') }}", formData, true)
        .done(function (response) {
            response = rptParseJson(response);
            if (response && response.success && response.category) {
                const c = response.category;
                rptAppendSelectOption(window.rptSelectIds.category, c.id, c.name, true);
                rptAppendSelectOption('rpt_category_parent', c.id, c.name, false);
                $('#rptAddCategoryModal').modal('hide');
                form[0].reset();
                const active = document.getElementById('rpt_category_is_active');
                if (active) active.checked = true;
                const preview = document.getElementById('rpt_category_image_preview');
                if (preview) preview.src = '{{ url('backend/assets/images/preview_image.png') }}';
                rptClearFormError('rptCategoryFormError', form[0]);
                return;
            }
            rptShowFormError('rptCategoryFormError', (response && response.message) || @json(__('Could not save category.')), form[0]);
        })
        .fail(function (xhr) {
            rptHandleSaveFail('rptCategoryFormError', form[0], xhr);
        })
        .always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        });

    return false;
}

$(document).on('input change', '#rptAddSupplierForm input, #rptAddSupplierForm select, #rptAddSupplierForm textarea, #rptAddCustomerForm input, #rptAddCustomerForm select, #rptAddCustomerForm textarea, #rptAddBranchForm input, #rptAddBranchForm select, #rptAddBranchForm textarea, #rptAddCategoryForm input, #rptAddCategoryForm select, #rptAddCategoryForm textarea', function () {
    $(this).removeClass('is-invalid');
    $(this).siblings('.rpt-field-error').remove();
    $(this).closest('.form-check').next('.rpt-field-error').remove();
});
</script>
@endpush
@endonce
