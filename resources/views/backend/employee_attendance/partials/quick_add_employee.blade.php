{{-- Attendance: Employee quick-add (silent AJAX, teal header, nested Dept/Role) --}}
<style>
    .att-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .att-field-group > .form-control,
    .att-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .att-field-group > .btn-att-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .att-form-error {
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

    .att-form-error.is-visible {
        display: block;
    }

    .att-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .att-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .att-field-group > .btn-att-add {
            min-width: 38px;
        }
    }
</style>

@can('create_employees')
    <div class="modal fade att-quick-modal" id="attAddEmployeeModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Employee</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="attAddEmployeeForm" class="att-quick-form" onsubmit="return saveAttEmployee(event)"
                        novalidate>
                        @csrf
                        <div class="att-form-error" id="attEmployeeFormError" role="alert"></div>
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
                                <div class="input-group nest-field-group">
                                    <select name="branch_id" id="attEmpBranch" class="form-control" required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_branches')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openAttBranchModal()" title="Add Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Department *</label>
                                <div class="input-group nest-field-group">
                                    <select name="department_id" id="attEmpDepartmentSelect" class="form-control" required>
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
                                    <select name="role" id="attEmpRoleSelect" class="form-control" required>
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
                                <input type="number" name="salary" class="form-control" required step="any"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Max Commission</label>
                                <input type="number" name="max_commission" class="form-control" step="any"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Sale Target</label>
                                <input type="number" name="sale_target" class="form-control" step="any"
                                    placeholder="0.00">
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="att_emp_status"
                                        name="status" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="att_emp_status">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveAttEmployee">
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
    <div class="modal fade att-quick-modal" id="attAddBranchModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="attAddBranchForm" class="att-quick-form" onsubmit="return saveAttBranch(event)"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="att-form-error" id="attBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="attBranchManagerSelect" class="form-control" required>
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
                                    <select name="account_id" id="attBranchAccountSelect" class="form-control" required>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="att_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="att_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveAttBranch">
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
        'managerSelectId' => 'attBranchManagerSelect',
        'accountSelectId' => 'attBranchAccountSelect',
        'departmentSelectId' => 'attEmpDepartmentSelect',
        'roleSelectId' => 'attEmpRoleSelect',
        'branchParentModalId' => 'attAddBranchModal',
        'employeeParentModalId' => 'attAddEmployeeModal',
        'roles' => $employeeRoles ?? [],
    ],
])

@push('js')
<script>
    (function () {
        if (window.__attEmpQuickAddInstalled) return;
        window.__attEmpQuickAddInstalled = true;

        const attParentModalId = 'attendanceModal';
        let attSuppressEmpResume = false;
        let attPausedEmployeeForBranch = false;

        ['attAddEmployeeModal', 'attAddBranchModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        if (!window.__attEmpAlertGuardInstalled) {
            window.__attEmpAlertGuardInstalled = true;
            window.__attEmpSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__attEmpSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__attEmpSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__attEmpSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        }

        function attMutePopups(on) {
            window.__attEmpSilent = !!on;

            if (typeof window.showFloatingAlert === 'function' && !window.__attFloatingWrapped) {
                window.__attFloatingWrapped = true;
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__attEmpSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }

            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__attToastrBackup) {
                    window.__attToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__attToastrBackup) {
                toastr.success = window.__attToastrBackup.success;
                toastr.error = window.__attToastrBackup.error;
                toastr.info = window.__attToastrBackup.info;
                toastr.warning = window.__attToastrBackup.warning;
            }
        }

        function attGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function attHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === attParentModalId && window.attendanceModalInstance) {
                window.attendanceModalInstance.hide();
                return;
            }
            const bs = attGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function attShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === attParentModalId && window.attendanceModalInstance) {
                window.attendanceModalInstance.show();
                return;
            }
            const bs = attGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function attClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        let attPausedParent = false;
        let attResumeScheduled = false;

        function attResumeParentModal() {
            if (!attPausedParent || attResumeScheduled) return;
            attPausedParent = false;
            attResumeScheduled = true;
            setTimeout(function () {
                attResumeScheduled = false;
                attClearStrayBackdrops();
                attShowModal(attParentModalId);
            }, 200);
        }

        function attOpenOverParent(quickModalId) {
            const parentEl = document.getElementById(attParentModalId);
            let opened = false;
            const openQuick = function () {
                if (opened) return;
                opened = true;
                attShowModal(quickModalId);
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                (typeof $ !== 'undefined' && $(parentEl).is(':visible'))
            );
            if (parentVisible) {
                attPausedParent = true;
                attResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                attHideModal(attParentModalId);
                setTimeout(function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                }, 450);
            } else {
                attPausedParent = false;
                openQuick();
            }
        }

        function attOpenBranchOverEmployee() {
            const empEl = document.getElementById('attAddEmployeeModal');
            let opened = false;
            const openBranch = function () {
                if (opened) return;
                opened = true;
                attShowModal('attAddBranchModal');
            };
            const empVisible = empEl && (
                empEl.classList.contains('show') ||
                (typeof $ !== 'undefined' && $(empEl).is(':visible'))
            );
            if (empVisible) {
                attSuppressEmpResume = true;
                attPausedEmployeeForBranch = true;
                const onEmpHidden = function () {
                    empEl.removeEventListener('hidden.bs.modal', onEmpHidden);
                    openBranch();
                };
                empEl.addEventListener('hidden.bs.modal', onEmpHidden);
                attHideModal('attAddEmployeeModal');
                setTimeout(function () {
                    empEl.removeEventListener('hidden.bs.modal', onEmpHidden);
                    openBranch();
                }, 450);
            } else {
                attPausedEmployeeForBranch = false;
                openBranch();
            }
        }

        function attResumeEmployeeAfterBranch() {
            setTimeout(function () {
                if (!attPausedEmployeeForBranch) return;
                const nestOpen = document.querySelector('#nxNestManagerModal.show, #nxNestAccountModal.show');
                if (nestOpen) return;
                const branchEl = document.getElementById('attAddBranchModal');
                if (branchEl && branchEl.classList.contains('show')) return;
                attPausedEmployeeForBranch = false;
                attClearStrayBackdrops();
                attShowModal('attAddEmployeeModal');
                attSuppressEmpResume = false;
            }, 350);
        }

        const empModal = document.getElementById('attAddEmployeeModal');
        if (empModal && !empModal.dataset.attResumeBound) {
            empModal.dataset.attResumeBound = '1';
            empModal.addEventListener('hidden.bs.modal', function () {
                if (attSuppressEmpResume) return;
                attResumeParentModal();
            });
        }

        const branchModal = document.getElementById('attAddBranchModal');
        if (branchModal && !branchModal.dataset.attEmpResumeBound) {
            branchModal.dataset.attEmpResumeBound = '1';
            branchModal.addEventListener('hidden.bs.modal', function () {
                attResumeEmployeeAfterBranch();
            });
        }

        function attEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function attShowFormError(errorId, message, formEl) {
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
                '<div class="att-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + attEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + attEscapeHtml(m) + '</li>';
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

        function attClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function attMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function attExtractErrorPayload(xhr) {
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
            return {
                messages: [@json(__('Something went wrong. Please try again.'))],
                errors: null
            };
        }

        function attHandleSaveFail(errorId, formEl, xhr) {
            const payload = attExtractErrorPayload(xhr);
            attClearFormError(errorId, formEl);
            if (payload.errors) attMarkFieldErrors(formEl, payload.errors);
            attShowFormError(errorId, payload.messages, formEl);
        }

        function attAppendOption(selectId, id, text, selectIt) {
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

        function attAppendEmployeeOption(id, name, phone, selectIt) {
            const el = document.querySelector('#attendanceModal #employee_id') ||
                document.getElementById('employee_id');
            if (!el || id == null) return;
            const value = String(id);
            const label = name + (phone ? ' (' + phone + ')' : '');
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

        function attParseJson(response) {
            if (typeof response === 'string') {
                try {
                    return JSON.parse(response);
                } catch (e) {
                    return null;
                }
            }
            return response;
        }

        window.openAttEmployeeModal = function () {
            const form = document.getElementById('attAddEmployeeForm');
            if (!form) return;
            form.reset();
            attClearFormError('attEmployeeFormError', form);
            const status = document.getElementById('att_emp_status');
            if (status) status.checked = true;
            const join = form.querySelector('[name="joining_date"]');
            if (join) join.value = @json(date('Y-m-d'));
            attOpenOverParent('attAddEmployeeModal');
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.openAttBranchModal = function () {
            const form = document.getElementById('attAddBranchForm');
            if (!form) return;
            form.reset();
            attClearFormError('attBranchFormError', form);
            const active = document.getElementById('att_branch_is_active');
            if (active) active.checked = true;
            attOpenBranchOverEmployee();
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveAttBranch = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#attAddBranchForm');
            const btn = $('#btnSaveAttBranch');
            if (!form.length) return false;

            attClearFormError('attBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            attMutePopups(true);

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
                    response = attParseJson(response);
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            attShowFormError('attBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('att_branch_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        attAppendOption('attEmpBranch', b.id, b.name, true);
                        attHideModal('attAddBranchModal');
                        form[0].reset();
                        const active = document.getElementById('att_branch_is_active');
                        if (active) active.checked = true;
                        attClearFormError('attBranchFormError', form[0]);
                        return;
                    }
                    attShowFormError('attBranchFormError',
                        (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function (xhr) {
                    attHandleSaveFail('attBranchFormError', form[0], xhr);
                })
                .always(function () {
                    attMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveAttEmployee = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#attAddEmployeeForm');
            const btn = $('#btnSaveAttEmployee');
            if (!form.length) return false;

            attClearFormError('attEmployeeFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            attMutePopups(true);

            const phone = String(form.find('[name="phone"]').val() || '').trim();
            const name = String(form.find('[name="name"]').val() || '').trim();

            $.ajax({
                url: "{{ route('employees.store') }}",
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .done(function (response) {
                    if (response && response.success && response.employee) {
                        const emp = response.employee;
                        if (emp.status === false) {
                            attShowFormError('attEmployeeFormError',
                                @json(__('Employee saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const status = document.getElementById('att_emp_status');
                            if (status) status.checked = true;
                            return;
                        }
                        attAppendEmployeeOption(emp.id, emp.name || name, phone, true);
                        attHideModal('attAddEmployeeModal');
                        form[0].reset();
                        const status = document.getElementById('att_emp_status');
                        if (status) status.checked = true;
                        attClearFormError('attEmployeeFormError', form[0]);
                        return;
                    }
                    attShowFormError('attEmployeeFormError',
                        (response && response.message) || @json(__('Could not save employee.')), form[0]);
                })
                .fail(function (xhr) {
                    attHandleSaveFail('attEmployeeFormError', form[0], xhr);
                })
                .always(function () {
                    attMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
