<style>
    .emp-field-group .btn-emp-add {
        min-width: 42px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .emp-field-group:has(.btn-emp-add) .form-select,
    .emp-field-group:has(.btn-emp-add) .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .emp-form-error {
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

    .emp-form-error.is-visible {
        display: block;
    }

    .emp-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .emp-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .emp-field-group .btn-emp-add {
            min-width: 38px;
        }
    }
</style>

{{-- Quick Add Branch --}}
@can('create_branches')
    <div class="modal fade emp-quick-modal" id="empAddBranchModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="empAddBranchForm" onsubmit="return saveEmpBranch(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="emp-form-error" id="empBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="empBranchManagerSelect" class="form-control" required>
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
                                    <select name="account_id" id="empBranchAccountSelect" class="form-control" required>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="emp_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="emp_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveEmpBranch">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Quick Add Role --}}
@can('manage_roles')
    <div class="modal fade emp-quick-modal" id="empAddRoleModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Add Role</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="empAddRoleForm" onsubmit="return saveEmpRole(event)">
                        @csrf
                        <div class="emp-form-error" id="empRoleFormError" role="alert"></div>
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
                        <p class="small text-muted mb-3 mb-md-2">
                            Permissions can be assigned later from Roles &amp; Permissions.
                        </p>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveEmpRole">
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
        'managerSelectId' => 'empBranchManagerSelect',
        'accountSelectId' => 'empBranchAccountSelect',
        'departmentSelectId' => 'empUnusedDepartmentSelect',
        'roleSelectId' => 'empUnusedRoleSelect',
        'branchParentModalId' => 'empAddBranchModal',
        'employeeParentModalId' => 'empAddBranchModal',
        'roles' => $roles ?? $employeeRoles ?? [],
    ],
])

<script>
    (function () {
        ['empAddBranchModal', 'empAddRoleModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
            if (typeof window.empBindQuickResume === 'function') {
                window.empBindQuickResume(id);
            }
        });

        function empEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function empShowFormError(errorId, message, formEl) {
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
                '<div class="emp-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + empEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + empEscapeHtml(m) + '</li>';
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

        function empClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.emp-field-error').remove();
            }
        }

        function empMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block emp-field-error';
                feedback.textContent = msg;
                input.insertAdjacentElement('afterend', feedback);
            });
        }

        function empExtractErrorPayload(xhr) {
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

        function empHandleSaveFail(errorId, formEl, xhr) {
            const payload = empExtractErrorPayload(xhr);
            empClearFormError(errorId, formEl);
            if (payload.errors) empMarkFieldErrors(formEl, payload.errors);
            empShowFormError(errorId, payload.messages, formEl);
        }

        function empAppendOption(selectId, id, text, selectIt) {
            const el = document.querySelector('#employeeModal #' + selectId) || document.getElementById(selectId);
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

        function empPostJson(url, data, isFormData) {
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

        window.openEmpBranchModal = function () {
            const form = document.getElementById('empAddBranchForm');
            if (!form) return;
            form.reset();
            empClearFormError('empBranchFormError', form);
            const active = document.getElementById('emp_branch_is_active');
            if (active) active.checked = true;
            const showBranch = function () {
                $('#empAddBranchModal').modal('show');
            };
            if (typeof window.empOpenOverEmployeeModal === 'function') {
                window.empOpenOverEmployeeModal(showBranch);
            } else {
                showBranch();
            }
        };

        window.openEmpRoleModal = function () {
            const form = document.getElementById('empAddRoleForm');
            if (!form) return;
            form.reset();
            empClearFormError('empRoleFormError', form);
            const showRole = function () {
                $('#empAddRoleModal').modal('show');
            };
            if (typeof window.empOpenOverEmployeeModal === 'function') {
                window.empOpenOverEmployeeModal(showRole);
            } else {
                showRole();
            }
        };

        window.saveEmpBranch = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#empAddBranchForm');
            const btn = $('#btnSaveEmpBranch');
            if (!form.length) return false;

            empClearFormError('empBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            empPostJson("{{ route('branches.store') }}", new FormData(form[0]), true)
                .done(function (response) {
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            empShowFormError('empBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        empAppendOption('branch_id', b.id, b.name, true);
                        $('#empAddBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('emp_branch_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    empShowFormError('empBranchFormError', (response && response.message) || @json(__('Could not save branch.')),
                        form[0]);
                })
                .fail(function (xhr) {
                    empHandleSaveFail('empBranchFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveEmpRole = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#empAddRoleForm');
            const btn = $('#btnSaveEmpRole');
            if (!form.length) return false;

            empClearFormError('empRoleFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            empPostJson("{{ route('roles.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.role) {
                        const r = response.role;
                        empAppendOption('role', r.name, r.name, true);
                        $('#empAddRoleModal').modal('hide');
                        form[0].reset();
                        return;
                    }
                    empShowFormError('empRoleFormError', (response && response.message) || @json(__('Could not save role.')),
                        form[0]);
                })
                .fail(function (xhr) {
                    empHandleSaveFail('empRoleFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
