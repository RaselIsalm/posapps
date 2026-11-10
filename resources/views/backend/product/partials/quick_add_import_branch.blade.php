{{-- Product Import: Branch silent quick-add (full fields + Manager/Account nest) --}}
<style>
    .imp-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .imp-field-group > .form-control,
    .imp-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .imp-field-group > .btn-imp-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .imp-form-error {
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

    .imp-form-error.is-visible {
        display: block;
    }

    .imp-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .imp-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .imp-field-group > .btn-imp-add {
            min-width: 38px;
        }
    }
</style>

@can('create_branches')
    <div class="modal fade" id="impAddBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="impAddBranchForm" onsubmit="return saveImpBranch(event)" enctype="multipart/form-data"
                        novalidate>
                        @csrf
                        <div class="imp-form-error" id="impBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="impBranchManagerSelect" class="form-control" required>
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
                                    <select name="account_id" id="impBranchAccountSelect" class="form-control" required>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="imp_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="imp_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveImpBranch">
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
        'managerSelectId' => 'impBranchManagerSelect',
        'accountSelectId' => 'impBranchAccountSelect',
        'departmentSelectId' => 'impUnusedDepartmentSelect',
        'roleSelectId' => 'impUnusedRoleSelect',
        'branchParentModalId' => 'impAddBranchModal',
        'employeeParentModalId' => 'impAddBranchModal',
        'roles' => $employeeRoles ?? [],
    ],
])

@push('js')
<script>
    (function () {
        if (window.__impBranchQuickAddInstalled) return;
        window.__impBranchQuickAddInstalled = true;

        const modalEl = document.getElementById('impAddBranchModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__impAlertGuardInstalled) {
            window.__impAlertGuardInstalled = true;
            window.__impQuickAddSilent = false;
            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__impQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };
            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__impQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__impQuickAddSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }
        }

        function impMutePopups(on) {
            window.__impQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__impToastrBackup) {
                    window.__impToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__impToastrBackup) {
                toastr.success = window.__impToastrBackup.success;
                toastr.error = window.__impToastrBackup.error;
                toastr.info = window.__impToastrBackup.info;
                toastr.warning = window.__impToastrBackup.warning;
            }
        }

        function impGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function impHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = impGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function impShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = impGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function impEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function impShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) messages = message.filter(Boolean);
            else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];
            let html = '<div class="imp-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) html += '<div>' + impEscapeHtml(messages[0]) + '</div>';
            else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + impEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
        }

        function impClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) $(formEl).find('.is-invalid').removeClass('is-invalid');
        }

        function impMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function impExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try { payload = JSON.parse(xhr.responseText); } catch (e) { payload = null; }
            }
            if (xhr.status === 419) {
                return { messages: [@json(__('Your session has expired. Please refresh the page and try again.'))], errors: null };
            }
            if (xhr.status === 403) {
                return { messages: [(payload && payload.message) || @json(__('You do not have permission.'))], errors: null };
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
            return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
        }

        function impAppendOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const value = String(id);
            const label = text || ('#' + value);
            let exists = false;
            for (let i = 0; i < el.options.length; i++) {
                if (el.options[i].value === value) {
                    el.options[i].text = label;
                    exists = true;
                    break;
                }
            }
            if (!exists) el.appendChild(new Option(label, value, false, false));
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }
        }

        window.openImpBranchModal = function () {
            const form = document.getElementById('impAddBranchForm');
            if (!form) return;
            form.reset();
            impClearFormError('impBranchFormError', form);
            const active = document.getElementById('imp_branch_is_active');
            if (active) active.checked = true;
            impShowModal('impAddBranchModal');
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveImpBranch = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#impAddBranchForm');
            const btn = $('#btnSaveImpBranch');
            if (!form.length) return false;

            impClearFormError('impBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            impMutePopups(true);

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
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            impShowFormError('impBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        impAppendOption('branch_id', b.id, b.name, true);
                        impHideModal('impAddBranchModal');
                        form[0].reset();
                        const active = document.getElementById('imp_branch_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    impShowFormError('impBranchFormError',
                        (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function (xhr) {
                    const payload = impExtractErrorPayload(xhr);
                    impClearFormError('impBranchFormError', form[0]);
                    if (payload.errors) impMarkFieldErrors(form[0], payload.errors);
                    impShowFormError('impBranchFormError', payload.messages, form[0]);
                })
                .always(function () {
                    impMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
