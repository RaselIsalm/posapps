@php
    $paymentMethodSelectId = $paymentMethodSelectId ?? 'pur_payment_method';
    // Optional: when set (e.g. duePaymentModal), close parent while account modal is open, then reopen on cancel/save.
    $parentModalId = $parentModalId ?? null;
@endphp

<style>
    .pur-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .pur-field-group > .form-control,
    .pur-field-group > .form-select {
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
    }

    .pur-field-group .btn-pur-add {
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

    .pur-field-group:has(.btn-pur-add) > .form-control,
    .pur-field-group:has(.btn-pur-add) > .form-select {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .pur-form-error {
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

    .pur-form-error.is-visible {
        display: block;
    }

    .pur-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .pur-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .pur-account-settings {
        border: 1px solid #eef1f4;
        border-radius: 10px;
        background: #fafbfc;
        padding: 10px 12px;
    }

    .pur-account-setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
    }

    .pur-account-setting-row + .pur-account-setting-row {
        border-top: 1px solid #eef1f4;
    }

    .pur-account-setting-copy label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #374151;
        margin: 0;
    }

    .pur-account-setting-copy p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.35;
    }

    #purAddAccountModal .modal-dialog {
        width: calc(100% - 1.5rem);
        max-width: 560px;
        margin: 0.75rem auto;
    }

    @media (max-width: 575.98px) {
        .pur-field-group .btn-pur-add {
            min-width: 38px;
            width: 38px;
            flex-basis: 38px;
        }

        #purAddAccountModal .modal-dialog {
            max-width: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #purAddAccountModal .modal-body {
            max-height: min(75dvh, 520px);
            overflow-y: auto;
        }

        .pur-account-setting-row {
            align-items: flex-start;
        }
    }
</style>

@can('create_accounts')
    <div class="modal fade" id="purAddAccountModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="purAddAccountForm" onsubmit="return savePurAccount(event)" novalidate>
                        @csrf
                        <div class="pur-form-error" id="purAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_account_name">Name *</label>
                                <input type="text" name="name" id="pur_account_name" class="form-control" required
                                    placeholder="Account name" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_account_type">Type *</label>
                                <select name="type" id="pur_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_account_number">
                                    Account Number <span id="pur_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="pur_account_number" class="form-control"
                                    placeholder="Not required for cash" autocomplete="off">
                                <small id="pur_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_account_initial_balance">Initial Balance</label>
                                <input type="number" name="initial_balance" id="pur_account_initial_balance" class="form-control"
                                    step="0.01" min="0" value="0" placeholder="0.00">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_account_note">Note</label>
                                <textarea name="note" id="pur_account_note" class="form-control" rows="2"
                                    placeholder="Optional note"></textarea>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="pur-account-settings">
                                    <div class="pur-account-setting-row">
                                        <div class="pur-account-setting-copy">
                                            <label for="pur_account_is_active">Active</label>
                                            <p>Available in purchases and other transactions.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="pur_account_is_active" name="is_active" value="on" checked
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="pur-account-setting-row">
                                        <div class="pur-account-setting-copy">
                                            <label for="pur_account_is_default">Default payment method</label>
                                            <p>Automatically preferred at checkout when available.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="pur_account_is_default" name="is_default" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="pur-account-setting-row">
                                        <div class="pur-account-setting-copy">
                                            <label for="pur_account_manual">Enter amount yourself</label>
                                            <p>On = type paid amount. Off = use product/order total where supported.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="pur_account_manual" name="allow_manual_amount" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSavePurAccount">
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
        const purAccountTargetSelect = '#{{ $paymentMethodSelectId }}';
        const purParentModalId = @json($parentModalId);
        let purPausedParentModalId = null;
        let purResumeScheduled = false;

        const modalEl = document.getElementById('purAddAccountModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Hard rule: Purchase Account quick-add must NEVER show alert / toastr / toast.
        if (!window.__purAlertGuardInstalled) {
            window.__purAlertGuardInstalled = true;
            window.__purQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__purQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__purQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        }

        function purMutePopups(on) {
            window.__purQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__purToastrBackup) {
                    window.__purToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__purToastrBackup) {
                toastr.success = window.__purToastrBackup.success;
                toastr.error = window.__purToastrBackup.error;
                toastr.info = window.__purToastrBackup.info;
                toastr.warning = window.__purToastrBackup.warning;
            }
        }

        function purGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function purHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = purGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function purShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = purGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        // Same pattern as Gift Card / Quick Sale: close parent while nested quick-add is open.
        function purClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function purResumeParentModal() {
            if (!purPausedParentModalId || purResumeScheduled) return;
            const parentId = purPausedParentModalId;
            purPausedParentModalId = null;
            purResumeScheduled = true;
            setTimeout(function () {
                purResumeScheduled = false;
                purClearStrayBackdrops();
                purShowModal(parentId);
            }, 200);
        }

        function purOpenAccountOverParent() {
            if (!purParentModalId) {
                purShowModal('purAddAccountModal');
                return;
            }

            const parentEl = document.getElementById(purParentModalId);
            const openQuick = function () {
                purShowModal('purAddAccountModal');
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                $(parentEl).is(':visible')
            );

            if (parentVisible) {
                purPausedParentModalId = purParentModalId;
                purResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                purHideModal(purParentModalId);
            } else {
                purPausedParentModalId = null;
                openQuick();
            }
        }

        if (modalEl && purParentModalId && !modalEl.dataset.purParentResumeBound) {
            modalEl.dataset.purParentResumeBound = '1';
            modalEl.addEventListener('hidden.bs.modal', function () {
                purResumeParentModal();
            });
        }

        function purEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function purShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="pur-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + purEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + purEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function purClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.pur-field-error').remove();
            }
        }

        function purMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            // Highlight invalid fields only — message stays in the top alert (DRY, no duplicate text).
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function purExtractErrorPayload(xhr) {
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

        function purHandleSaveFail(errorId, formEl, xhr) {
            const payload = purExtractErrorPayload(xhr);
            purClearFormError(errorId, formEl);
            if (payload.errors) purMarkFieldErrors(formEl, payload.errors);
            purShowFormError(errorId, payload.messages, formEl);
        }

        function purAppendOption(selector, id, text, selectIt) {
            const el = document.querySelector(selector);
            if (!el || id == null) return;
            const value = String(id);
            const label = text || ('#' + value);
            let opt = el.querySelector('option[value="' + value.replace(/"/g, '\\"') + '"]');
            if (!opt) {
                opt = new Option(label, value, false, false);
                // Keep list sorted by name for easier scanning
                const options = Array.from(el.options);
                let inserted = false;
                for (let i = 0; i < options.length; i++) {
                    if ((options[i].text || '').localeCompare(label, undefined, { sensitivity: 'base' }) > 0) {
                        el.insertBefore(opt, options[i]);
                        inserted = true;
                        break;
                    }
                }
                if (!inserted) el.appendChild(opt);
            } else {
                opt.text = label;
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }
        }

        function purPostJson(url, data) {
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

        function syncPurAccountNumberRequirement() {
            const typeEl = document.getElementById('pur_account_type');
            const numberEl = document.getElementById('pur_account_number');
            const requiredMark = document.getElementById('pur_account_number_required');
            const hintEl = document.getElementById('pur_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.openPurAccountModal = function () {
            const form = document.getElementById('purAddAccountForm');
            if (!form) return;
            form.reset();
            purClearFormError('purAccountFormError', form);
            const active = document.getElementById('pur_account_is_active');
            if (active) active.checked = true;
            const def = document.getElementById('pur_account_is_default');
            if (def) def.checked = false;
            const manual = document.getElementById('pur_account_manual');
            if (manual) manual.checked = false;
            const typeEl = document.getElementById('pur_account_type');
            if (typeEl) typeEl.value = 'cash';
            const bal = document.getElementById('pur_account_initial_balance');
            if (bal) bal.value = '0';
            syncPurAccountNumberRequirement();
            purOpenAccountOverParent();
            setTimeout(function () {
                const nameEl = document.getElementById('pur_account_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.savePurAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#purAddAccountForm');
            const btn = $('#btnSavePurAccount');
            if (!form.length) return false;

            purClearFormError('purAccountFormError', form[0]);
            syncPurAccountNumberRequirement();

            const typeEl = document.getElementById('pur_account_type');
            const numberEl = document.getElementById('pur_account_number');
            if (typeEl && numberEl && typeEl.value !== 'cash' && !String(numberEl.value || '').trim()) {
                purMarkFieldErrors(form[0], { account_number: [@json(__('Account number is required for this type.'))] });
                purShowFormError('purAccountFormError', @json(__('Account number is required for this type.')), form[0]);
                return false;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            purMutePopups(true);

            purPostJson("{{ route('accounts.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            purShowFormError('purAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        purAppendOption(purAccountTargetSelect, a.id, a.name, true);
                        purHideModal('purAddAccountModal');
                        form[0].reset();
                        const active = document.getElementById('pur_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    purShowFormError('purAccountFormError',
                        (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    purHandleSaveFail('purAccountFormError', form[0], xhr);
                })
                .always(function () {
                    purMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('change.purAccType', '#pur_account_type')
            .on('change.purAccType', '#pur_account_type', syncPurAccountNumberRequirement);
    })();
</script>
@endpush
