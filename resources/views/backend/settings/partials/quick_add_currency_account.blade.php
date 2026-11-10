{{-- General Settings: Currency + Default Payment (Account) silent quick-add --}}
<style>
    .gs-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .gs-field-group > .form-control,
    .gs-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .gs-field-group > .btn-gs-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .gs-form-error {
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

    .gs-form-error.is-visible {
        display: block;
    }

    .gs-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .gs-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .gs-account-settings {
        border: 1px solid #eef1f4;
        border-radius: 10px;
        background: #fafbfc;
        padding: 10px 12px;
    }

    .gs-account-setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
    }

    .gs-account-setting-row + .gs-account-setting-row {
        border-top: 1px solid #eef1f4;
    }

    .gs-account-setting-copy label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #374151;
        margin: 0;
    }

    .gs-account-setting-copy p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.35;
    }

    #gsAddAccountModal .modal-dialog {
        width: calc(100% - 1.5rem);
        max-width: 560px;
        margin: 0.75rem auto;
    }

    @media (max-width: 575.98px) {
        .gs-field-group > .btn-gs-add {
            min-width: 38px;
        }

        #gsAddAccountModal .modal-dialog,
        #gsAddCurrencyModal .modal-dialog {
            max-width: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #gsAddAccountModal .modal-body,
        #gsAddCurrencyModal .modal-body {
            max-height: min(75dvh, 520px);
            overflow-y: auto;
        }

        .gs-account-setting-row {
            align-items: flex-start;
        }
    }
</style>

@can('create_currencies')
    <div class="modal fade gs-quick-modal" id="gsAddCurrencyModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-coins me-2"></i>Add Currency</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="gsAddCurrencyForm" class="gs-quick-form" onsubmit="return saveGsCurrency(event)"
                        novalidate>
                        @csrf
                        <div class="gs-form-error" id="gsCurrencyFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_currency_name">Name *</label>
                                <input type="text" name="name" id="gs_currency_name" class="form-control" required
                                    placeholder="Currency Name" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_currency_code">Code *</label>
                                <input type="text" name="code" id="gs_currency_code" class="form-control" required
                                    placeholder="Currency Code" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_currency_symbol">Symbol *</label>
                                <input type="text" name="symbol" id="gs_currency_symbol" class="form-control" required
                                    placeholder="Currency Symbol" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_currency_rate">Exchange Rate *</label>
                                <input type="number" step="any" name="exchange_rate" id="gs_currency_rate"
                                    class="form-control" value="1" required placeholder="Exchange Rate">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_currency_position">Position</label>
                                <select name="position" id="gs_currency_position" class="form-control">
                                    <option value="1" selected>Prefix (e.g. {{ \App\Helpers\CurrencyHelper::getSymbol() }}100.00)</option>
                                    <option value="2">Suffix (e.g. 100.00{{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="gs_currency_is_active" name="is_active" value="1" checked
                                        style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="gs_currency_is_active">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveGsCurrency">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@can('create_accounts')
    <div class="modal fade gs-quick-modal" id="gsAddAccountModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="gsAddAccountForm" class="gs-quick-form" onsubmit="return saveGsAccount(event)"
                        novalidate>
                        @csrf
                        <div class="gs-form-error" id="gsAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_account_name">Name *</label>
                                <input type="text" name="name" id="gs_account_name" class="form-control" required
                                    placeholder="Account name" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_account_type">Type *</label>
                                <select name="type" id="gs_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_account_number">
                                    Account Number <span id="gs_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="gs_account_number" class="form-control"
                                    placeholder="Not required for cash" autocomplete="off">
                                <small id="gs_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_account_initial_balance">Initial Balance</label>
                                <input type="number" name="initial_balance" id="gs_account_initial_balance"
                                    class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="gs_account_note">Note</label>
                                <textarea name="note" id="gs_account_note" class="form-control" rows="2"
                                    placeholder="Optional note"></textarea>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="gs-account-settings">
                                    <div class="gs-account-setting-row">
                                        <div class="gs-account-setting-copy">
                                            <label for="gs_account_is_active">Active</label>
                                            <p>Available in checkout and other transactions.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="gs_account_is_active" name="is_active" value="on" checked
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="gs-account-setting-row">
                                        <div class="gs-account-setting-copy">
                                            <label for="gs_account_is_default">Default payment method</label>
                                            <p>Automatically preferred at checkout when available.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="gs_account_is_default" name="is_default" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                    <div class="gs-account-setting-row">
                                        <div class="gs-account-setting-copy">
                                            <label for="gs_account_manual">Enter amount yourself</label>
                                            <p>On = type paid amount. Off = use product/order total where supported.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="gs_account_manual" name="allow_manual_amount" value="on"
                                                style="width: 40px; height: 20px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveGsAccount">
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
        if (window.__gsCurrencyAccountQuickAddInstalled) return;
        window.__gsCurrencyAccountQuickAddInstalled = true;

        ['gsAddCurrencyModal', 'gsAddAccountModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        if (!window.__gsAlertGuardInstalled) {
            window.__gsAlertGuardInstalled = true;
            window.__gsQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__gsQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__gsQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        }

        function gsMutePopups(on) {
            window.__gsQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__gsToastrBackup) {
                    window.__gsToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__gsToastrBackup) {
                toastr.success = window.__gsToastrBackup.success;
                toastr.error = window.__gsToastrBackup.error;
                toastr.info = window.__gsToastrBackup.info;
                toastr.warning = window.__gsToastrBackup.warning;
            }
        }

        function gsGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function gsHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = gsGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function gsShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = gsGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function gsEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function gsShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="gs-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + gsEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + gsEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function gsClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.gs-field-error').remove();
            }
        }

        function gsMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function gsExtractErrorPayload(xhr) {
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

        function gsHandleSaveFail(errorId, formEl, xhr) {
            const payload = gsExtractErrorPayload(xhr);
            gsClearFormError(errorId, formEl);
            if (payload.errors) gsMarkFieldErrors(formEl, payload.errors);
            gsShowFormError(errorId, payload.messages, formEl);
        }

        function gsAppendOption(selectId, value, text, selectIt) {
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

        function gsPostJson(url, data) {
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

        function syncGsAccountNumberRequirement() {
            const typeEl = document.getElementById('gs_account_type');
            const numberEl = document.getElementById('gs_account_number');
            const requiredMark = document.getElementById('gs_account_number_required');
            const hintEl = document.getElementById('gs_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.openGsCurrencyModal = function () {
            const form = document.getElementById('gsAddCurrencyForm');
            if (!form) return;
            form.reset();
            gsClearFormError('gsCurrencyFormError', form);
            const active = document.getElementById('gs_currency_is_active');
            if (active) active.checked = true;
            const rate = document.getElementById('gs_currency_rate');
            if (rate) rate.value = '1';
            const position = document.getElementById('gs_currency_position');
            if (position) position.value = '1';
            gsShowModal('gsAddCurrencyModal');
            setTimeout(function () {
                const nameEl = document.getElementById('gs_currency_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveGsCurrency = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#gsAddCurrencyForm');
            const btn = $('#btnSaveGsCurrency');
            if (!form.length) return false;

            gsClearFormError('gsCurrencyFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            gsMutePopups(true);

            gsPostJson("{{ route('currencies.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.currency) {
                        const c = response.currency;
                        if (c.is_active === false) {
                            gsAppendOption('currency', c.code, c.code, false);
                            gsShowFormError('gsCurrencyFormError',
                                @json(__('Currency saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        gsAppendOption('currency', c.code, c.code, true);
                        gsHideModal('gsAddCurrencyModal');
                        form[0].reset();
                        const active = document.getElementById('gs_currency_is_active');
                        if (active) active.checked = true;
                        const rate = document.getElementById('gs_currency_rate');
                        if (rate) rate.value = '1';
                        return;
                    }
                    gsShowFormError('gsCurrencyFormError',
                        (response && response.message) || @json(__('Could not save currency.')), form[0]);
                })
                .fail(function (xhr) {
                    gsHandleSaveFail('gsCurrencyFormError', form[0], xhr);
                })
                .always(function () {
                    gsMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.openGsAccountModal = function () {
            const form = document.getElementById('gsAddAccountForm');
            if (!form) return;
            form.reset();
            gsClearFormError('gsAccountFormError', form);
            const active = document.getElementById('gs_account_is_active');
            if (active) active.checked = true;
            const def = document.getElementById('gs_account_is_default');
            if (def) def.checked = false;
            const manual = document.getElementById('gs_account_manual');
            if (manual) manual.checked = false;
            const typeEl = document.getElementById('gs_account_type');
            if (typeEl) typeEl.value = 'cash';
            const bal = document.getElementById('gs_account_initial_balance');
            if (bal) bal.value = '0';
            syncGsAccountNumberRequirement();
            gsShowModal('gsAddAccountModal');
            setTimeout(function () {
                const nameEl = document.getElementById('gs_account_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveGsAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#gsAddAccountForm');
            const btn = $('#btnSaveGsAccount');
            if (!form.length) return false;

            gsClearFormError('gsAccountFormError', form[0]);
            syncGsAccountNumberRequirement();

            const typeEl = document.getElementById('gs_account_type');
            const numberEl = document.getElementById('gs_account_number');
            if (typeEl && numberEl && typeEl.value !== 'cash' && !String(numberEl.value || '').trim()) {
                gsMarkFieldErrors(form[0], { account_number: [@json(__('Account number is required for this type.'))] });
                gsShowFormError('gsAccountFormError', @json(__('Account number is required for this type.')), form[0]);
                return false;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            gsMutePopups(true);

            gsPostJson("{{ route('accounts.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            gsShowFormError('gsAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        const label = a.type ? (a.name + ' (' + a.type + ')') : a.name;
                        gsAppendOption('default_payment_method', a.id, label, true);
                        gsHideModal('gsAddAccountModal');
                        form[0].reset();
                        const active = document.getElementById('gs_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    gsShowFormError('gsAccountFormError',
                        (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    gsHandleSaveFail('gsAccountFormError', form[0], xhr);
                })
                .always(function () {
                    gsMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('change.gsAccType', '#gs_account_type')
            .on('change.gsAccType', '#gs_account_type', syncGsAccountNumberRequirement);
    })();
</script>
@endpush
