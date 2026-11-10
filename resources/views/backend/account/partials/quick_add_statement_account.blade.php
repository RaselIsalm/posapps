{{-- Account Statement filter: Account silent quick-add (full fields, nest over filter modal) --}}
@php
    $accountSelectId = $accountSelectId ?? 'stmt_account_id';
    $parentModalId = $parentModalId ?? 'filterModal';
@endphp

<style>
    .stmt-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .stmt-field-group > .form-control,
    .stmt-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .stmt-field-group > .btn-stmt-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .stmt-form-error {
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

    .stmt-form-error.is-visible {
        display: block;
    }

    .stmt-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stmt-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .stmt-account-settings {
        border: 1px solid #eef1f4;
        border-radius: 10px;
        background: #fafbfc;
        padding: 10px 12px;
    }

    .stmt-account-setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
    }

    .stmt-account-setting-row + .stmt-account-setting-row {
        border-top: 1px solid #eef1f4;
    }

    .stmt-account-setting-copy label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #374151;
        margin: 0;
    }

    .stmt-account-setting-copy p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.35;
    }

    #stmtAddAccountModal .modal-dialog {
        width: calc(100% - 1.5rem);
        max-width: 560px;
        margin: 0.75rem auto;
    }

    @media (max-width: 575.98px) {
        .stmt-field-group > .btn-stmt-add {
            min-width: 38px;
        }

        #stmtAddAccountModal .modal-dialog {
            max-width: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #stmtAddAccountModal .modal-body {
            max-height: min(75dvh, 520px);
            overflow-y: auto;
        }

        .stmt-account-setting-row {
            align-items: flex-start;
        }
    }
</style>

@can('create_accounts')
    <div class="modal fade" id="stmtAddAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Account</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="stmtAddAccountForm" onsubmit="return saveStmtAccount(event)" novalidate>
                        @csrf
                        <div class="stmt-form-error" id="stmtAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="stmt_account_name">Name *</label>
                                <input type="text" name="name" id="stmt_account_name" class="form-control" required
                                    placeholder="Account name" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="stmt_account_type">Type *</label>
                                <select name="type" id="stmt_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="stmt_account_number">
                                    Account Number <span id="stmt_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="stmt_account_number" class="form-control"
                                    placeholder="Not required for cash" autocomplete="off">
                                <small id="stmt_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="stmt_account_initial_balance">Initial Balance</label>
                                <input type="number" name="initial_balance" id="stmt_account_initial_balance"
                                    class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="stmt_account_note">Note</label>
                                <textarea name="note" id="stmt_account_note" class="form-control" rows="2"
                                    placeholder="Optional note"></textarea>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="stmt-account-settings">
                                    <div class="stmt-account-setting-row">
                                        <div class="stmt-account-setting-copy">
                                            <label for="stmt_account_is_active">Active</label>
                                            <p>Available in statements and other transactions.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="stmt_account_is_active" name="is_active" value="on" checked
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="stmt-account-setting-row">
                                        <div class="stmt-account-setting-copy">
                                            <label for="stmt_account_is_default">Default payment method</label>
                                            <p>Automatically preferred at checkout when available.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="stmt_account_is_default" name="is_default" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="stmt-account-setting-row">
                                        <div class="stmt-account-setting-copy">
                                            <label for="stmt_account_manual">Enter amount yourself</label>
                                            <p>On = type paid amount. Off = use product/order total where supported.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="stmt_account_manual" name="allow_manual_amount" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveStmtAccount">
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
    (function () {
        if (window.__stmtAccountQuickAddInstalled) return;
        window.__stmtAccountQuickAddInstalled = true;

        const stmtAccountSelectId = @json($accountSelectId);
        const stmtParentModalId = @json($parentModalId);
        let stmtPausedParentModalId = null;
        let stmtResumeScheduled = false;

        const modalEl = document.getElementById('stmtAddAccountModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__stmtAlertGuardInstalled) {
            window.__stmtAlertGuardInstalled = true;
            window.__stmtQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__stmtQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__stmtQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloat = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__stmtQuickAddSilent) return;
                    return nativeFloat.apply(window, arguments);
                };
            }
        }

        function stmtMutePopups(on) {
            window.__stmtQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__stmtToastrBackup) {
                    window.__stmtToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__stmtToastrBackup) {
                toastr.success = window.__stmtToastrBackup.success;
                toastr.error = window.__stmtToastrBackup.error;
                toastr.info = window.__stmtToastrBackup.info;
                toastr.warning = window.__stmtToastrBackup.warning;
            }
        }

        function stmtGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function stmtHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = stmtGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function stmtShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = stmtGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function stmtClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function stmtResumeParentModal() {
            if (!stmtPausedParentModalId || stmtResumeScheduled) return;
            const parentId = stmtPausedParentModalId;
            stmtPausedParentModalId = null;
            stmtResumeScheduled = true;
            setTimeout(function () {
                stmtResumeScheduled = false;
                stmtClearStrayBackdrops();
                stmtShowModal(parentId);
            }, 200);
        }

        function stmtOpenAccountOverParent() {
            if (!stmtParentModalId) {
                stmtShowModal('stmtAddAccountModal');
                return;
            }
            const parentEl = document.getElementById(stmtParentModalId);
            const openQuick = function () {
                stmtShowModal('stmtAddAccountModal');
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                $(parentEl).is(':visible')
            );
            if (parentVisible) {
                stmtPausedParentModalId = stmtParentModalId;
                stmtResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                stmtHideModal(stmtParentModalId);
            } else {
                stmtPausedParentModalId = null;
                openQuick();
            }
        }

        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                stmtResumeParentModal();
            });
        }

        function stmtEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function stmtShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="stmt-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + stmtEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + stmtEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function stmtClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function stmtMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function stmtExtractErrorPayload(xhr) {
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

        function stmtHandleSaveFail(errorId, formEl, xhr) {
            const payload = stmtExtractErrorPayload(xhr);
            stmtClearFormError(errorId, formEl);
            if (payload.errors) stmtMarkFieldErrors(formEl, payload.errors);
            stmtShowFormError(errorId, payload.messages, formEl);
        }

        function stmtAppendOption(selectId, value, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || value == null || value === '') return;
            const optValue = String(value);
            const label = text || optValue;
            let exists = false;
            for (let i = 0; i < el.options.length; i++) {
                if (el.options[i].value === optValue) {
                    el.options[i].text = label;
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                el.appendChild(new Option(label, optValue, false, false));
            }
            if (selectIt) {
                el.value = optValue;
                $(el).trigger('change');
            }
        }

        function stmtAccountLabel(account) {
            const name = account.name || 'Account';
            const num = account.account_number ? String(account.account_number) : '';
            if (!num) return name;
            return name + ' (' + num.charAt(0).toUpperCase() + num.slice(1) + ')';
        }

        function stmtPostJson(url, data) {
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

        function syncStmtAccountNumberRequirement() {
            const typeEl = document.getElementById('stmt_account_type');
            const numberEl = document.getElementById('stmt_account_number');
            const requiredMark = document.getElementById('stmt_account_number_required');
            const hintEl = document.getElementById('stmt_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.openStmtAccountModal = function () {
            const form = document.getElementById('stmtAddAccountForm');
            if (!form) return;
            form.reset();
            stmtClearFormError('stmtAccountFormError', form);
            const active = document.getElementById('stmt_account_is_active');
            if (active) active.checked = true;
            const def = document.getElementById('stmt_account_is_default');
            if (def) def.checked = false;
            const manual = document.getElementById('stmt_account_manual');
            if (manual) manual.checked = false;
            const typeEl = document.getElementById('stmt_account_type');
            if (typeEl) typeEl.value = 'cash';
            const bal = document.getElementById('stmt_account_initial_balance');
            if (bal) bal.value = '0';
            syncStmtAccountNumberRequirement();
            stmtOpenAccountOverParent();
            setTimeout(function () {
                const nameEl = document.getElementById('stmt_account_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveStmtAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#stmtAddAccountForm');
            const btn = $('#btnSaveStmtAccount');
            if (!form.length) return false;

            stmtClearFormError('stmtAccountFormError', form[0]);
            syncStmtAccountNumberRequirement();

            const typeEl = document.getElementById('stmt_account_type');
            const numberEl = document.getElementById('stmt_account_number');
            if (typeEl && numberEl && typeEl.value !== 'cash' && !String(numberEl.value || '').trim()) {
                stmtMarkFieldErrors(form[0], { account_number: [@json(__('Account number is required for this type.'))] });
                stmtShowFormError('stmtAccountFormError', @json(__('Account number is required for this type.')), form[0]);
                return false;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            stmtMutePopups(true);

            stmtPostJson("{{ route('accounts.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            stmtShowFormError('stmtAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        stmtAppendOption(stmtAccountSelectId, a.id, stmtAccountLabel(a), true);
                        stmtHideModal('stmtAddAccountModal');
                        form[0].reset();
                        const active = document.getElementById('stmt_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    stmtShowFormError('stmtAccountFormError',
                        (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    stmtHandleSaveFail('stmtAccountFormError', form[0], xhr);
                })
                .always(function () {
                    stmtMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('change.stmtAccType', '#stmt_account_type')
            .on('change.stmtAccType', '#stmt_account_type', syncStmtAccountNumberRequirement);
    })();
</script>
@endpush
