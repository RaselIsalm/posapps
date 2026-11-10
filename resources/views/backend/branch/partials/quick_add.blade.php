@push('css')
    <style>
        .br-field-group {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100%;
        }

        .br-field-group .btn-br-add {
            min-width: 42px;
            height: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .br-field-group:has(.btn-br-add) .form-control,
        .br-field-group:has(.btn-br-add) .form-select {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            flex: 1 1 auto;
            min-width: 0;
        }

        .br-form-error {
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

        .br-form-error.is-visible {
            display: block;
        }

        .br-form-error-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .br-form-error ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        @media (max-width: 575.98px) {
            .br-field-group .btn-br-add {
                min-width: 38px;
            }
        }
    </style>
@endpush

{{-- Add Manager (User) Modal --}}
@can('manage_users')
    <div class="modal fade" id="brAddManagerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Manager</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="brAddManagerForm" onsubmit="return saveBrManager(event)">
                        @csrf
                        <div class="br-form-error" id="brManagerFormError" role="alert"></div>
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
                                <div class="input-group br-field-group">
                                    <select name="role" id="br_manager_role" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach ($roles ?? [] as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_roles')
                                        <button type="button" class="btn btn-outline-primary btn-br-add"
                                            onclick="openBrRoleModal()" title="Add New Role">
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
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBrManager">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Role (nested over Add Manager) --}}
@can('manage_roles')
    <div class="modal fade" id="brAddRoleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Add Role</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="brAddRoleForm" onsubmit="return saveBrRole(event)">
                        @csrf
                        <div class="br-form-error" id="brRoleFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Role Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Role name"
                                maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional description" maxlength="1000"></textarea>
                        </div>
                        <p class="small text-muted mb-3">
                            Permissions can be assigned later from Roles &amp; Permissions.
                        </p>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBrRole">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Add Account Modal --}}
@can('create_accounts')
    <div class="modal fade" id="brAddAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Account</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="brAddAccountForm" onsubmit="return saveBrAccount(event)">
                        @csrf
                        <div class="br-form-error" id="brAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Account name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Type *</label>
                                <select name="type" id="br_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">
                                    Account Number <span id="br_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="br_account_number" class="form-control"
                                    placeholder="Not required for cash">
                                <small id="br_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="br_account_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="br_account_is_active">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="br_account_is_default"
                                        name="is_default" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="br_account_is_default">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="br_account_manual"
                                        name="allow_manual_amount" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="br_account_manual">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBrAccount">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@push('js')
    <script>
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
        $('.branch-menu').addClass('active');

        (function () {
            let brPausedManagerForRole = false;
            let brResumeManagerScheduled = false;

            ['brAddManagerModal', 'brAddRoleModal', 'brAddAccountModal'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });

            if (!window.__brAlertGuardInstalled) {
                window.__brAlertGuardInstalled = true;
                window.__brQuickAddSilent = false;

                const nativeAlert = window.alert;
                window.alert = function () {
                    if (window.__brQuickAddSilent) return;
                    return nativeAlert.apply(window, arguments);
                };

                if (typeof window.showAppToast === 'function') {
                    const nativeToast = window.showAppToast;
                    window.showAppToast = function () {
                        if (window.__brQuickAddSilent) return;
                        return nativeToast.apply(window, arguments);
                    };
                }
                if (typeof window.showFloatingAlert === 'function') {
                    const nativeFloat = window.showFloatingAlert;
                    window.showFloatingAlert = function () {
                        if (window.__brQuickAddSilent) return;
                        return nativeFloat.apply(window, arguments);
                    };
                }
            }

            function brMutePopups(on) {
                window.__brQuickAddSilent = !!on;
                if (typeof toastr === 'undefined') return;
                if (on) {
                    if (!window.__brToastrBackup) {
                        window.__brToastrBackup = {
                            success: toastr.success,
                            error: toastr.error,
                            info: toastr.info,
                            warning: toastr.warning
                        };
                    }
                    toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
                } else if (window.__brToastrBackup) {
                    toastr.success = window.__brToastrBackup.success;
                    toastr.error = window.__brToastrBackup.error;
                    toastr.info = window.__brToastrBackup.info;
                    toastr.warning = window.__brToastrBackup.warning;
                }
            }

            function brGetBsModal(el) {
                if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
                return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
            }

            function brHideModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                const bs = brGetBsModal(el);
                if (bs) bs.hide();
                else $('#' + id).modal('hide');
            }

            function brShowModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                const bs = brGetBsModal(el);
                if (bs) bs.show();
                else $('#' + id).modal('show');
            }

            function brClearStrayBackdrops() {
                document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                    b.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
            }

            function brResumeManagerModal() {
                if (!brPausedManagerForRole || brResumeManagerScheduled) return;
                brPausedManagerForRole = false;
                brResumeManagerScheduled = true;
                setTimeout(function () {
                    brResumeManagerScheduled = false;
                    brClearStrayBackdrops();
                    brShowModal('brAddManagerModal');
                }, 200);
            }

            function brOpenRoleOverManager() {
                const managerEl = document.getElementById('brAddManagerModal');
                const openRole = function () {
                    brShowModal('brAddRoleModal');
                };
                const managerVisible = managerEl && (
                    managerEl.classList.contains('show') ||
                    $(managerEl).is(':visible')
                );
                if (managerVisible) {
                    brPausedManagerForRole = true;
                    brResumeManagerScheduled = false;
                    const onHidden = function () {
                        managerEl.removeEventListener('hidden.bs.modal', onHidden);
                        openRole();
                    };
                    managerEl.addEventListener('hidden.bs.modal', onHidden);
                    brHideModal('brAddManagerModal');
                } else {
                    brPausedManagerForRole = false;
                    openRole();
                }
            }

            const brRoleModalEl = document.getElementById('brAddRoleModal');
            if (brRoleModalEl) {
                brRoleModalEl.addEventListener('hidden.bs.modal', function () {
                    brResumeManagerModal();
                });
            }

            window.brEscapeHtml = function (text) {
                return String(text == null ? '' : text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };

            window.brShowFormError = function (errorId, message, formEl) {
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
                    '<div class="br-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
                if (messages.length === 1) {
                    html += '<div>' + brEscapeHtml(messages[0]) + '</div>';
                } else {
                    html += '<ul>';
                    messages.forEach(function (m) {
                        html += '<li>' + brEscapeHtml(m) + '</li>';
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
            };

            window.brClearFormError = function (errorId, formEl) {
                const box = document.getElementById(errorId);
                if (box) {
                    box.classList.remove('is-visible');
                    box.innerHTML = '';
                }
                if (formEl) {
                    $(formEl).find('.is-invalid').removeClass('is-invalid');
                    $(formEl).find('.br-field-error').remove();
                }
            };

            window.brMarkFieldErrors = function (formEl, errors) {
                if (!formEl || !errors) return;
                Object.keys(errors).forEach(function (field) {
                    const input = formEl.querySelector('[name="' + field + '"]');
                    if (!input) return;
                    input.classList.add('is-invalid');
                    const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    if (!msg) return;
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block br-field-error';
                    feedback.textContent = msg;
                    input.insertAdjacentElement('afterend', feedback);
                });
            };

            window.brExtractErrorPayload = function (xhr) {
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
            };

            window.brHandleSaveFail = function (errorId, formEl, xhr) {
                const payload = brExtractErrorPayload(xhr);
                brClearFormError(errorId, formEl);
                if (payload.errors) brMarkFieldErrors(formEl, payload.errors);
                brShowFormError(errorId, payload.messages, formEl);
            };

            window.brAppendOption = function (selectId, id, text, selectIt) {
                const el = document.getElementById(selectId);
                if (!el || id == null) return;
                const value = String(id);
                if ($(el).find('option[value="' + value + '"]').length === 0) {
                    el.appendChild(new Option(text || ('#' + value), value, false, false));
                }
                if (selectIt) {
                    $(el).find('option[disabled]').prop('selected', false);
                    el.value = value;
                    $(el).trigger('change');
                }
            };

            window.brPostJson = function (url, data, isFormData) {
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
            };

            window.openBrManagerModal = function () {
                const form = document.getElementById('brAddManagerForm');
                if (!form) return;
                form.reset();
                brClearFormError('brManagerFormError', form);
                brShowModal('brAddManagerModal');
            };

            window.openBrRoleModal = function () {
                const form = document.getElementById('brAddRoleForm');
                if (!form) return;
                form.reset();
                brClearFormError('brRoleFormError', form);
                brOpenRoleOverManager();
                setTimeout(function () {
                    const name = form.querySelector('[name="name"]');
                    if (name) name.focus();
                }, 350);
            };

            window.openBrAccountModal = function () {
                const form = document.getElementById('brAddAccountForm');
                if (!form) return;
                form.reset();
                brClearFormError('brAccountFormError', form);
                const active = document.getElementById('br_account_is_active');
                if (active) active.checked = true;
                const typeEl = document.getElementById('br_account_type');
                if (typeEl) typeEl.value = 'cash';
                syncBrAccountNumberRequirement();
                brShowModal('brAddAccountModal');
            };

            window.saveBrManager = function (e) {
                e.preventDefault();
                e.stopPropagation();
                const form = $('#brAddManagerForm');
                const btn = $('#btnSaveBrManager');
                if (!form.length) return false;

                brClearFormError('brManagerFormError', form[0]);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                brMutePopups(true);

                brPostJson("{{ route('users.store') }}", form.serialize(), false)
                    .done(function (response) {
                        if (response && response.success && response.user) {
                            const u = response.user;
                            brAppendOption('manager_id', u.id, u.name, true);
                            brHideModal('brAddManagerModal');
                            form[0].reset();
                            return;
                        }
                        brShowFormError('brManagerFormError', (response && response.message) || @json(__('Could not save manager.')), form[0]);
                    })
                    .fail(function (xhr) {
                        brHandleSaveFail('brManagerFormError', form[0], xhr);
                    })
                    .always(function () {
                        brMutePopups(false);
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                    });

                return false;
            };

            window.saveBrRole = function (e) {
                e.preventDefault();
                e.stopPropagation();
                const form = $('#brAddRoleForm');
                const btn = $('#btnSaveBrRole');
                if (!form.length) return false;

                brClearFormError('brRoleFormError', form[0]);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                brMutePopups(true);

                brPostJson("{{ route('roles.store') }}", form.serialize(), false)
                    .done(function (response) {
                        if (response && response.success && response.role) {
                            const r = response.role;
                            brAppendOption('br_manager_role', r.name, r.name, true);
                            brHideModal('brAddRoleModal');
                            form[0].reset();
                            return;
                        }
                        brShowFormError('brRoleFormError', (response && response.message) || @json(__('Could not save role.')), form[0]);
                    })
                    .fail(function (xhr) {
                        brHandleSaveFail('brRoleFormError', form[0], xhr);
                    })
                    .always(function () {
                        brMutePopups(false);
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                    });

                return false;
            };

            window.syncBrAccountNumberRequirement = function () {
                const typeEl = document.getElementById('br_account_type');
                const numberEl = document.getElementById('br_account_number');
                const requiredMark = document.getElementById('br_account_number_required');
                const hintEl = document.getElementById('br_account_number_hint');
                if (!typeEl || !numberEl) return;
                const isCash = typeEl.value === 'cash';
                numberEl.required = !isCash;
                numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
                if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
                if (hintEl) hintEl.classList.toggle('d-none', !isCash);
            };

            window.saveBrAccount = function (e) {
                e.preventDefault();
                e.stopPropagation();
                const form = $('#brAddAccountForm');
                const btn = $('#btnSaveBrAccount');
                if (!form.length) return false;

                brClearFormError('brAccountFormError', form[0]);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                brMutePopups(true);

                brPostJson("{{ route('accounts.store') }}", form.serialize(), false)
                    .done(function (response) {
                        if (response && response.success && response.account) {
                            const a = response.account;
                            if (!a.is_active) {
                                brShowFormError('brAccountFormError',
                                    @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                                return;
                            }
                            brAppendOption('account_id', a.id, a.name, true);
                            brHideModal('brAddAccountModal');
                            form[0].reset();
                            const active = document.getElementById('br_account_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        brShowFormError('brAccountFormError', (response && response.message) || @json(__('Could not save account.')), form[0]);
                    })
                    .fail(function (xhr) {
                        brHandleSaveFail('brAccountFormError', form[0], xhr);
                    })
                    .always(function () {
                        brMutePopups(false);
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                    });

                return false;
            };

            $(document).off('change.brAccType', '#br_account_type')
                .on('change.brAccType', '#br_account_type', syncBrAccountNumberRequirement);
        })();
    </script>
@endpush
