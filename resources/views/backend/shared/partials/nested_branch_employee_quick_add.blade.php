{{--
  Nested chicken-egg quick-add:
  Branch → Manager / Account
  Employee/Salesman → Department / Role

  Config (set before include or via $nx):
    managerSelectId, accountSelectId, departmentSelectId, roleSelectId
    branchParentModalId, employeeParentModalId
    roles (for Manager Role dropdown)
--}}
@php
    $nx = $nx ?? [];
    $nxManagerSelectId = $nx['managerSelectId'] ?? ($nxManagerSelectId ?? 'nxBranchManagerSelect');
    $nxAccountSelectId = $nx['accountSelectId'] ?? ($nxAccountSelectId ?? 'nxBranchAccountSelect');
    $nxDepartmentSelectId = $nx['departmentSelectId'] ?? ($nxDepartmentSelectId ?? 'nxEmployeeDepartmentSelect');
    $nxRoleSelectId = $nx['roleSelectId'] ?? ($nxRoleSelectId ?? 'nxEmployeeRoleSelect');
    $nxBranchParentModalId = $nx['branchParentModalId'] ?? ($nxBranchParentModalId ?? 'addBranchModal');
    $nxEmployeeParentModalId = $nx['employeeParentModalId'] ?? ($nxEmployeeParentModalId ?? 'addEmployeeModal');
    $nxRoles = $nx['roles'] ?? ($nxRoles ?? ($employeeRoles ?? ($roles ?? [])));
@endphp

<style>
    .nest-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .nest-field-group > .form-control,
    .nest-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .nest-field-group > .btn-nest-add {
        min-width: 42px;
        height: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .nest-form-error {
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

    .nest-form-error.is-visible {
        display: block;
    }

    .nest-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .nest-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .nest-field-group > .btn-nest-add {
            min-width: 38px;
        }
    }
</style>

{{-- Add Manager (User) --}}
@can('manage_users')
    <div class="modal fade nest-quick-modal" id="nxNestManagerModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Manager</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="nxNestManagerForm" onsubmit="return saveNxNestManager(event)">
                        @csrf
                        <div class="nest-form-error" id="nxNestManagerFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Full name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Email *</label>
                                <input type="email" name="email" class="form-control" required placeholder="Login email">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="Phone number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Role *</label>
                                <div class="nest-field-group">
                                    <select name="role" id="nx_nest_manager_role" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach ($nxRoles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_roles')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestRoleModal()" title="Add New Role">
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
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveNxNestManager">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Account --}}
@can('create_accounts')
    <div class="modal fade nest-quick-modal" id="nxNestAccountModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Account</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="nxNestAccountForm" onsubmit="return saveNxNestAccount(event)">
                        @csrf
                        <div class="nest-form-error" id="nxNestAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Account name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Type *</label>
                                <select name="type" id="nx_nest_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">
                                    Account Number <span id="nx_nest_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="nx_nest_account_number" class="form-control"
                                    placeholder="Not required for cash">
                                <small id="nx_nest_account_number_hint" class="text-muted">Cash accounts do not need an account
                                    number.</small>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="nx_nest_account_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="nx_nest_account_is_active">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="nx_nest_account_is_default"
                                        name="is_default" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="nx_nest_account_is_default">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="nx_nest_account_manual"
                                        name="allow_manual_amount" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="nx_nest_account_manual">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveNxNestAccount">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Department --}}
@can('create_departments')
    <div class="modal fade nest-quick-modal" id="nxNestDepartmentModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-sitemap me-2"></i>Add Department</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="nxNestDepartmentForm" onsubmit="return saveNxNestDepartment(event)">
                        @csrf
                        <div class="nest-form-error" id="nxNestDepartmentFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Department Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Department name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Department Code *</label>
                            <div class="input-group">
                                <input type="text" name="code" id="nx_nest_dept_code" class="form-control" required
                                    placeholder="DEP-12345">
                                <button type="button" class="btn btn-outline-primary" id="nxNestGenerateDeptCodeBtn">
                                    Generate
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="nx_nest_dept_is_active"
                                    name="is_active" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="nx_nest_dept_is_active">Yes</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveNxNestDepartment">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Role --}}
@can('manage_roles')
    <div class="modal fade nest-quick-modal" id="nxNestRoleModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Add Role</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="nxNestRoleForm" onsubmit="return saveNxNestRole(event)">
                        @csrf
                        <div class="nest-form-error" id="nxNestRoleFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Role Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Role name"
                                maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional description"></textarea>
                        </div>
                        <p class="small text-muted mb-3">
                            Permissions can be assigned later from Roles &amp; Permissions.
                        </p>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveNxNestRole">
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
        if (window.__nxNestQuickAddInstalled) return;
        window.__nxNestQuickAddInstalled = true;

        window.__nxNestConfig = {
            managerSelectId: @json($nxManagerSelectId),
            accountSelectId: @json($nxAccountSelectId),
            departmentSelectId: @json($nxDepartmentSelectId),
            roleSelectId: @json($nxRoleSelectId),
            branchParentModalId: @json($nxBranchParentModalId),
            employeeParentModalId: @json($nxEmployeeParentModalId)
        };

        window.nxConfigureNestQuickAdd = function (cfg) {
            if (!cfg || typeof cfg !== 'object') return;
            Object.keys(cfg).forEach(function (key) {
                if (cfg[key] != null) window.__nxNestConfig[key] = cfg[key];
            });
        };

        ['nxNestManagerModal', 'nxNestAccountModal', 'nxNestDepartmentModal', 'nxNestRoleModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        // Silent: no toaster / alert during nest quick-add
        if (!window.__nxAlertGuardInstalled) {
            window.__nxAlertGuardInstalled = true;
            window.__nxNestSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__nxNestSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__nxNestSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }

            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__nxNestSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }
        }

        function nxMutePopups(on) {
            window.__nxNestSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__nxToastrBackup) {
                    window.__nxToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__nxToastrBackup) {
                toastr.success = window.__nxToastrBackup.success;
                toastr.error = window.__nxToastrBackup.error;
                toastr.info = window.__nxToastrBackup.info;
                toastr.warning = window.__nxToastrBackup.warning;
            }
        }

        function nxGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function nxHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = nxGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function nxShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = nxGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function nxClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        let nxPausedParentModalId = null;
        let nxResumeScheduled = false;
        let nxPausedManagerForRole = false;
        let nxResumeManagerScheduled = false;

        function nxResumeParentModal() {
            if (!nxPausedParentModalId || nxResumeScheduled) return;
            const parentId = nxPausedParentModalId;
            nxPausedParentModalId = null;
            nxResumeScheduled = true;
            setTimeout(function () {
                nxResumeScheduled = false;
                nxClearStrayBackdrops();
                nxShowModal(parentId);
            }, 200);
        }

        function nxResumeManagerModal() {
            if (!nxPausedManagerForRole || nxResumeManagerScheduled) return;
            nxPausedManagerForRole = false;
            nxResumeManagerScheduled = true;
            setTimeout(function () {
                nxResumeManagerScheduled = false;
                nxClearStrayBackdrops();
                nxShowModal('nxNestManagerModal');
            }, 200);
        }

        function nxOpenOverParent(quickModalId, parentModalId) {
            const parentEl = document.getElementById(parentModalId);
            const openQuick = function () {
                nxShowModal(quickModalId);
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                $(parentEl).is(':visible')
            );
            if (parentVisible) {
                nxPausedParentModalId = parentModalId;
                nxResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                nxHideModal(parentModalId);
            } else {
                nxPausedParentModalId = null;
                openQuick();
            }
        }

        function nxOpenRoleOverManager() {
            const managerEl = document.getElementById('nxNestManagerModal');
            const openRole = function () {
                nxShowModal('nxNestRoleModal');
            };
            const managerVisible = managerEl && (
                managerEl.classList.contains('show') ||
                $(managerEl).is(':visible')
            );
            if (managerVisible) {
                nxPausedManagerForRole = true;
                nxResumeManagerScheduled = false;
                const onHidden = function () {
                    managerEl.removeEventListener('hidden.bs.modal', onHidden);
                    openRole();
                };
                managerEl.addEventListener('hidden.bs.modal', onHidden);
                nxHideModal('nxNestManagerModal');
            } else {
                nxPausedManagerForRole = false;
                nxOpenOverParent('nxNestRoleModal', window.__nxNestConfig.employeeParentModalId);
            }
        }

        const nxManagerModalEl = document.getElementById('nxNestManagerModal');
        if (nxManagerModalEl) {
            nxManagerModalEl.addEventListener('hidden.bs.modal', function () {
                if (nxPausedManagerForRole) return;
                nxResumeParentModal();
            });
        }
        ['nxNestAccountModal', 'nxNestDepartmentModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('hidden.bs.modal', function () {
                nxResumeParentModal();
            });
        });
        const nxRoleModalEl = document.getElementById('nxNestRoleModal');
        if (nxRoleModalEl) {
            nxRoleModalEl.addEventListener('hidden.bs.modal', function () {
                if (nxPausedManagerForRole) {
                    nxResumeManagerModal();
                    return;
                }
                nxResumeParentModal();
            });
        }

        function nxEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function nxShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) {
                    return m.trim();
                }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html =
                '<div class="nest-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + nxEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + nxEscapeHtml(m) + '</li>';
                });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try {
                    box.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                } catch (e) {}
            }
        }

        function nxClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.nest-field-error').remove();
            }
        }

        function nxMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
            });
        }

        function nxExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (e) {
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
                return {
                    messages: [payload.message],
                    errors: payload.errors || null
                };
            }
            if (xhr.status === 0) {
                return {
                    messages: [@json(__('Network error. Please check your connection.'))],
                    errors: null
                };
            }
            return {
                messages: [@json(__('Something went wrong. Please try again.'))],
                errors: null
            };
        }

        function nxHandleSaveFail(errorId, formEl, xhr) {
            const payload = nxExtractErrorPayload(xhr);
            nxClearFormError(errorId, formEl);
            if (payload.errors) nxMarkFieldErrors(formEl, payload.errors);
            nxShowFormError(errorId, payload.messages, formEl);
        }

        function nxAppendOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const value = String(id);
            let exists = false;
            for (let i = 0; i < el.options.length; i++) {
                if (el.options[i].value === value) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                el.appendChild(new Option(text || ('#' + value), value, false, false));
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }
        }

        function nxPostJson(url, data, isFormData) {
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
            return $.ajax(options);
        }

        function syncNxNestAccountNumberRequirement() {
            const typeEl = document.getElementById('nx_nest_account_type');
            const numberEl = document.getElementById('nx_nest_account_number');
            const requiredMark = document.getElementById('nx_nest_account_number_required');
            const hintEl = document.getElementById('nx_nest_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.openNxNestManagerModal = function () {
            const form = document.getElementById('nxNestManagerForm');
            if (!form) return;
            form.reset();
            nxClearFormError('nxNestManagerFormError', form);
            nxOpenOverParent('nxNestManagerModal', window.__nxNestConfig.branchParentModalId);
        };

        window.openNxNestAccountModal = function () {
            const form = document.getElementById('nxNestAccountForm');
            if (!form) return;
            form.reset();
            nxClearFormError('nxNestAccountFormError', form);
            const active = document.getElementById('nx_nest_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('nx_nest_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncNxNestAccountNumberRequirement();
            nxOpenOverParent('nxNestAccountModal', window.__nxNestConfig.branchParentModalId);
        };

        window.openNxNestDepartmentModal = function () {
            const form = document.getElementById('nxNestDepartmentForm');
            if (!form) return;
            form.reset();
            nxClearFormError('nxNestDepartmentFormError', form);
            const active = document.getElementById('nx_nest_dept_is_active');
            if (active) active.checked = true;
            const code = document.getElementById('nx_nest_dept_code');
            if (code) code.value = 'DEP-' + Math.floor(10000 + Math.random() * 90000);
            nxOpenOverParent('nxNestDepartmentModal', window.__nxNestConfig.employeeParentModalId);
        };

        window.openNxNestRoleModal = function () {
            const form = document.getElementById('nxNestRoleForm');
            if (!form) return;
            form.reset();
            nxClearFormError('nxNestRoleFormError', form);
            nxOpenRoleOverManager();
            setTimeout(function () {
                const name = form.querySelector('[name="name"]');
                if (name) name.focus();
            }, 350);
        };

        window.saveNxNestManager = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#nxNestManagerForm');
            const btn = $('#btnSaveNxNestManager');
            if (!form.length) return false;

            nxClearFormError('nxNestManagerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            nxMutePopups(true);

            nxPostJson("{{ route('users.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.user) {
                        const u = response.user;
                        nxAppendOption(window.__nxNestConfig.managerSelectId, u.id, u.name, true);
                        nxHideModal('nxNestManagerModal');
                        form[0].reset();
                        return;
                    }
                    nxShowFormError('nxNestManagerFormError', (response && response.message) || @json(__('Could not save manager.')),
                        form[0]);
                })
                .fail(function (xhr) {
                    nxHandleSaveFail('nxNestManagerFormError', form[0], xhr);
                })
                .always(function () {
                    nxMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveNxNestAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#nxNestAccountForm');
            const btn = $('#btnSaveNxNestAccount');
            if (!form.length) return false;

            nxClearFormError('nxNestAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            nxMutePopups(true);

            nxPostJson("{{ route('accounts.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            nxShowFormError('nxNestAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        nxAppendOption(window.__nxNestConfig.accountSelectId, a.id, a.name, true);
                        // Also sync common payment / account selects on the same page when present
                        ['payment_account_id', 'account_id'].forEach(function (extraId) {
                            if (extraId === window.__nxNestConfig.accountSelectId) return;
                            const el = document.getElementById(extraId);
                            if (el && el.tagName === 'SELECT') {
                                nxAppendOption(extraId, a.id, a.name, false);
                            }
                        });
                        document.querySelectorAll('select.payment-method').forEach(function (el) {
                            if (el.id === window.__nxNestConfig.accountSelectId) return;
                            const value = String(a.id);
                            let exists = false;
                            for (let i = 0; i < el.options.length; i++) {
                                if (el.options[i].value === value) {
                                    exists = true;
                                    break;
                                }
                            }
                            if (!exists) el.appendChild(new Option(a.name, value, false, false));
                        });
                        nxHideModal('nxNestAccountModal');
                        form[0].reset();
                        const active = document.getElementById('nx_nest_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    nxShowFormError('nxNestAccountFormError', (response && response.message) || @json(__('Could not save account.')),
                        form[0]);
                })
                .fail(function (xhr) {
                    nxHandleSaveFail('nxNestAccountFormError', form[0], xhr);
                })
                .always(function () {
                    nxMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveNxNestDepartment = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#nxNestDepartmentForm');
            const btn = $('#btnSaveNxNestDepartment');
            if (!form.length) return false;

            nxClearFormError('nxNestDepartmentFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            nxMutePopups(true);

            nxPostJson("{{ route('departments.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.department) {
                        const d = response.department;
                        nxAppendOption(window.__nxNestConfig.departmentSelectId, d.id, d.name, true);
                        nxHideModal('nxNestDepartmentModal');
                        form[0].reset();
                        const active = document.getElementById('nx_nest_dept_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    nxShowFormError('nxNestDepartmentFormError', (response && response.message) ||
                        @json(__('Could not save department.')), form[0]);
                })
                .fail(function (xhr) {
                    nxHandleSaveFail('nxNestDepartmentFormError', form[0], xhr);
                })
                .always(function () {
                    nxMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveNxNestRole = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#nxNestRoleForm');
            const btn = $('#btnSaveNxNestRole');
            if (!form.length) return false;

            nxClearFormError('nxNestRoleFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            nxMutePopups(true);

            nxPostJson("{{ route('roles.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.role) {
                        const r = response.role;
                        const fromManager = !!nxPausedManagerForRole;
                        // Manager Role select (when nested from Add Manager)
                        nxAppendOption('nx_nest_manager_role', r.name, r.name, fromManager);
                        // Employee/Salesman role select uses role name as value
                        if (window.__nxNestConfig.roleSelectId) {
                            nxAppendOption(window.__nxNestConfig.roleSelectId, r.name, r.name, !fromManager);
                        }
                        nxHideModal('nxNestRoleModal');
                        form[0].reset();
                        return;
                    }
                    nxShowFormError('nxNestRoleFormError', (response && response.message) || @json(__('Could not save role.')),
                        form[0]);
                })
                .fail(function (xhr) {
                    nxHandleSaveFail('nxNestRoleFormError', form[0], xhr);
                })
                .always(function () {
                    nxMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).on('change', '#nx_nest_account_type', syncNxNestAccountNumberRequirement);
        $(document).on('click', '#nxNestGenerateDeptCodeBtn', function () {
            const code = document.getElementById('nx_nest_dept_code');
            if (code) code.value = 'DEP-' + Math.floor(10000 + Math.random() * 90000);
        });
    })();

    // Always refresh config when this partial is included (page-specific select IDs)
    if (typeof window.nxConfigureNestQuickAdd === 'function') {
        window.nxConfigureNestQuickAdd({
            managerSelectId: @json($nxManagerSelectId),
            accountSelectId: @json($nxAccountSelectId),
            departmentSelectId: @json($nxDepartmentSelectId),
            roleSelectId: @json($nxRoleSelectId),
            branchParentModalId: @json($nxBranchParentModalId),
            employeeParentModalId: @json($nxEmployeeParentModalId)
        });
    }
</script>
