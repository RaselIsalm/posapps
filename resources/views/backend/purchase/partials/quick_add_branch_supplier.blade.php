{{-- Purchase Edit: Branch + Supplier quick-add — teal header matches Account/Customer --}}

{{-- Quick Add Branch Modal --}}
@can('create_branches')
<div class="modal fade" id="quickBranchModal" tabindex="-1" aria-labelledby="quickBranchModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold" id="quickBranchModalTitle"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickBranchForm" onsubmit="return false;">
                <div class="modal-body pt-3">
                    <div class="pur-bs-form-error" id="purBranchFormError" role="alert"></div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="quick_branch_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_branch_name" name="name" placeholder="Name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_manager_id" class="form-label-sm">Manager <span class="text-danger">*</span></label>
                            <div class="input-group nest-field-group">
                                <select class="form-select" id="quick_manager_id" name="manager_id" required>
                                    <option value="" disabled selected>Select Manager</option>
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
                        <div class="col-12 mb-2">
                            <label for="quick_account_id" class="form-label-sm">Account <span class="text-danger">*</span></label>
                            <div class="input-group nest-field-group">
                                <select class="form-select" id="quick_account_id" name="account_id" required>
                                    <option value="" disabled selected>Select Account</option>
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
                        <div class="col-12 mb-2">
                            <label for="quick_branch_phone" class="form-label-sm">Phone</label>
                            <input type="text" class="form-control" id="quick_branch_phone" name="phone" placeholder="Phone">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_branch_email" class="form-label-sm">Email</label>
                            <input type="email" class="form-control" id="quick_branch_email" name="email" placeholder="Email">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_branch_address" class="form-label-sm">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_branch_address" name="address" placeholder="Address" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_branch_logo" class="form-label-sm">Branch Logo</label>
                            <input type="file" class="form-control" id="quick_branch_logo" name="branch_logo" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <label class="form-check-label mb-0 fw-bold" for="quick_branch_status">Is Active</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input switch-size-lg m-0" type="checkbox" checked role="switch" id="quick_branch_status" name="is_active" style="width: 45px; height: 22px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickBranchSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickBranchSpinner" role="status"></span>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@include('backend.shared.partials.nested_branch_employee_quick_add', [
    'nx' => [
        'managerSelectId' => 'quick_manager_id',
        'accountSelectId' => 'quick_account_id',
        'departmentSelectId' => 'purUnusedDepartmentSelect',
        'roleSelectId' => 'purUnusedRoleSelect',
        'branchParentModalId' => 'quickBranchModal',
        'employeeParentModalId' => 'quickBranchModal',
        'roles' => $employeeRoles ?? $roles ?? [],
    ],
])

{{-- Quick Add Supplier Modal --}}
@can('create_suppliers')
<div class="modal fade" id="quickSupplierModal" tabindex="-1" aria-labelledby="quickSupplierModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold" id="quickSupplierModalTitle"><i class="fas fa-truck me-2"></i>Add Supplier</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickSupplierForm" onsubmit="return false;">
                <div class="modal-body pt-3">
                    <div class="pur-bs-form-error" id="purSupplierFormError" role="alert"></div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_supplier_name" name="name" placeholder="Name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_company_name" class="form-label-sm">Company Name</label>
                            <input type="text" class="form-control" id="quick_supplier_company_name" name="company_name" placeholder="Company Name">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_phone" class="form-label-sm">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_supplier_phone" name="phone" placeholder="Phone" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_email" class="form-label-sm">Email</label>
                            <input type="email" class="form-control" id="quick_supplier_email" name="email" placeholder="Email">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_address" class="form-label-sm">Address</label>
                            <input type="text" class="form-control" id="quick_supplier_address" name="address" placeholder="Address">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_note" class="form-label-sm">Note</label>
                            <input type="text" class="form-control" id="quick_supplier_note" name="note" placeholder="Note">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_supplier_previous_due" class="form-label-sm">Previous Due</label>
                            <input type="number" step="any" class="form-control" id="quick_supplier_previous_due" name="previous_due" placeholder="Previous Due">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <label class="form-check-label mb-0 fw-bold" for="quick_supplier_status">Is Active</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input switch-size-lg m-0" type="checkbox" checked role="switch" id="quick_supplier_status" name="is_active" style="width: 45px; height: 22px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickSupplierSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickSupplierSpinner" role="status"></span>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('css')
<style>
    .pur-bs-field-group {
        display: flex;
        align-items: stretch;
        gap: 0;
    }
    .pur-bs-field-group > .form-select,
    .pur-bs-field-group > .form-control {
        flex: 1 1 auto;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        height: 45px;
    }
    .pur-bs-field-group .btn-pur-bs-add {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 42px;
        height: 45px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 0;
    }
    @media (max-width: 575.98px) {
        .pur-bs-field-group .btn-pur-bs-add {
            min-width: 38px;
        }
    }
    .pur-bs-form-error {
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
    .pur-bs-form-error.is-visible {
        display: block;
    }
    .pur-bs-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }
    .pur-bs-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }
    .pur-bs-field-error {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        if (window.__purBsAlertGuardInstalled) return;
        window.__purBsAlertGuardInstalled = true;
        window.__purBsQuickAddSilent = false;

        const nativeAlert = window.alert;
        window.alert = function () {
            if (window.__purBsQuickAddSilent) return;
            return nativeAlert.apply(window, arguments);
        };

        if (typeof window.showAppToast === 'function') {
            const nativeToast = window.showAppToast;
            window.showAppToast = function () {
                if (window.__purBsQuickAddSilent) return;
                return nativeToast.apply(window, arguments);
            };
        }
    })();

    function purBsMutePopups(on) {
        window.__purBsQuickAddSilent = !!on;
        if (typeof toastr !== 'undefined') {
            if (on) {
                if (!window.__purBsToastrBackup) {
                    window.__purBsToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__purBsToastrBackup) {
                toastr.success = window.__purBsToastrBackup.success;
                toastr.error = window.__purBsToastrBackup.error;
                toastr.info = window.__purBsToastrBackup.info;
                toastr.warning = window.__purBsToastrBackup.warning;
            }
        }
    }

    function purBsEscapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function purBsShowFormError(errorId, message, formEl) {
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
        if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

        let html = '<div class="pur-bs-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
        if (messages.length === 1) {
            html += '<div>' + purBsEscapeHtml(messages[0]) + '</div>';
        } else {
            html += '<ul>';
            messages.forEach(function (m) { html += '<li>' + purBsEscapeHtml(m) + '</li>'; });
            html += '</ul>';
        }
        box.innerHTML = html;
        box.classList.add('is-visible');
        if (formEl) {
            try { formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
        }
    }

    function purBsClearFormError(errorId, formEl) {
        const box = document.getElementById(errorId);
        if (box) {
            box.classList.remove('is-visible');
            box.innerHTML = '';
        }
        if (formEl) {
            $(formEl).find('.is-invalid').removeClass('is-invalid');
            $(formEl).find('.pur-bs-field-error').remove();
        }
    }

    function purBsMarkFieldErrors(formEl, errors) {
        if (!formEl || !errors) return;
        Object.keys(errors).forEach(function (field) {
            const input = formEl.querySelector('[name="' + field + '"]');
            if (!input) return;
            input.classList.add('is-invalid');
            const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            if (!msg) return;
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block pur-bs-field-error';
            feedback.textContent = msg;
            input.insertAdjacentElement('afterend', feedback);
        });
    }

    function purBsExtractError(xhr) {
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

    function purBsAppendOption(selectId, id, text) {
        const el = document.getElementById(selectId);
        if (!el || id == null) return;
        const $sel = $(el);
        const value = String(id);
        const label = text || ('#' + value);
        if ($sel.find('option[value="' + value + '"]').length === 0) {
            $sel.append(new Option(label, value, true, true));
        } else {
            $sel.val(value);
        }
        $sel.trigger('change');
    }

    function purBsPost(url, data) {
        purBsMutePopups(true);
        return $.ajax({
            url: url,
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).always(function () {
            purBsMutePopups(false);
        });
    }

    window.openQuickBranchModal = function () {
        const form = document.getElementById('quickBranchForm');
        if (!form) return;
        form.reset();
        purBsClearFormError('purBranchFormError', form);
        const status = document.getElementById('quick_branch_status');
        if (status) status.checked = true;
        $('#quickBranchModal').modal('show');
        setTimeout(function () { $('#quick_branch_name').focus(); }, 300);
    };

    window.openQuickSupplierModal = function () {
        const form = document.getElementById('quickSupplierForm');
        if (!form) return;
        form.reset();
        purBsClearFormError('purSupplierFormError', form);
        const status = document.getElementById('quick_supplier_status');
        if (status) status.checked = true;
        $('#quickSupplierModal').modal('show');
        setTimeout(function () { $('#quick_supplier_name').focus(); }, 300);
    };

    $(document).on('submit', '#quickBranchForm', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = this;
        const btn = $('#quickBranchSaveBtn');
        purBsClearFormError('purBranchFormError', form);

        const formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');
        if (!$('#quick_branch_status').is(':checked')) {
            formData.delete('is_active');
        }

        btn.prop('disabled', true);
        $('#quickBranchSpinner').removeClass('d-none');

        purBsPost("{{ route('branches.store') }}", formData)
            .done(function (response) {
                if (typeof response === 'string') {
                    try { response = JSON.parse(response); } catch (err) { response = null; }
                }
                if (response && response.success && response.branch) {
                    const b = response.branch;
                    if (b.status === false || b.status === 0) {
                        purBsShowFormError('purBranchFormError', @json(__('Branch saved, but it is inactive so it was not selected.')), form);
                        form.reset();
                        const status = document.getElementById('quick_branch_status');
                        if (status) status.checked = true;
                        return;
                    }
                    purBsAppendOption('branch_id', b.id, b.name);
                    $('#quickBranchModal').modal('hide');
                    form.reset();
                    const status = document.getElementById('quick_branch_status');
                    if (status) status.checked = true;
                    return;
                }
                purBsShowFormError('purBranchFormError', (response && response.message) || @json(__('Could not save branch.')), form);
            })
            .fail(function (xhr) {
                const payload = purBsExtractError(xhr);
                purBsClearFormError('purBranchFormError', form);
                if (payload.errors) purBsMarkFieldErrors(form, payload.errors);
                purBsShowFormError('purBranchFormError', payload.messages, form);
            })
            .always(function () {
                btn.prop('disabled', false);
                $('#quickBranchSpinner').addClass('d-none');
            });

        return false;
    });

    $(document).on('submit', '#quickSupplierForm', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = this;
        const btn = $('#quickSupplierSaveBtn');
        purBsClearFormError('purSupplierFormError', form);

        const formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');
        if (!$('#quick_supplier_status').is(':checked')) {
            formData.delete('is_active');
        }

        btn.prop('disabled', true);
        $('#quickSupplierSpinner').removeClass('d-none');

        purBsPost("{{ route('suppliers.store') }}", formData)
            .done(function (response) {
                if (typeof response === 'string') {
                    try { response = JSON.parse(response); } catch (err) { response = null; }
                }
                if (response && response.success && response.supplier) {
                    const s = response.supplier;
                    if (s.is_active === false || s.is_active === 0) {
                        purBsShowFormError('purSupplierFormError', @json(__('Supplier saved, but it is inactive so it was not selected.')), form);
                        form.reset();
                        const status = document.getElementById('quick_supplier_status');
                        if (status) status.checked = true;
                        return;
                    }
                    purBsAppendOption('supplier_id', s.id, s.name);
                    $('#quickSupplierModal').modal('hide');
                    form.reset();
                    const status = document.getElementById('quick_supplier_status');
                    if (status) status.checked = true;
                    return;
                }
                purBsShowFormError('purSupplierFormError', (response && response.message) || @json(__('Could not save supplier.')), form);
            })
            .fail(function (xhr) {
                const payload = purBsExtractError(xhr);
                purBsClearFormError('purSupplierFormError', form);
                if (payload.errors) purBsMarkFieldErrors(form, payload.errors);
                purBsShowFormError('purSupplierFormError', payload.messages, form);
            })
            .always(function () {
                btn.prop('disabled', false);
                $('#quickSupplierSpinner').addClass('d-none');
            });

        return false;
    });

    $(document).on('input change', '#quickBranchForm input, #quickBranchForm select, #quickSupplierForm input, #quickSupplierForm select', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.pur-bs-field-error').remove();
    });
</script>
@endpush
