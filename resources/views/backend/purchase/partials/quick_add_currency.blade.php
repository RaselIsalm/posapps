{{-- Purchase Create/Edit: Currency silent quick-add (full fields) --}}
@php
    $currencySelectId = $currencySelectId ?? 'currency_selector';
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

    #purAddCurrencyModal .modal-dialog {
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

        #purAddCurrencyModal .modal-dialog {
            max-width: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #purAddCurrencyModal .modal-body {
            max-height: min(75dvh, 520px);
            overflow-y: auto;
        }
    }
</style>

@can('create_currencies')
    <div class="modal fade" id="purAddCurrencyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-coins me-2"></i>Add Currency</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="purAddCurrencyForm" onsubmit="return savePurCurrency(event)" novalidate>
                        @csrf
                        <div class="pur-form-error" id="purCurrencyFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_currency_name">Name *</label>
                                <input type="text" name="name" id="pur_currency_name" class="form-control" required
                                    placeholder="Currency Name" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_currency_code">Code *</label>
                                <input type="text" name="code" id="pur_currency_code" class="form-control" required
                                    placeholder="Currency Code" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_currency_symbol">Symbol *</label>
                                <input type="text" name="symbol" id="pur_currency_symbol" class="form-control" required
                                    placeholder="Currency Symbol" autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_currency_rate">Exchange Rate *</label>
                                <input type="number" step="any" name="exchange_rate" id="pur_currency_rate"
                                    class="form-control" value="1" required placeholder="Exchange Rate">
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1" for="pur_currency_position">Position</label>
                                <select name="position" id="pur_currency_position" class="form-control">
                                    <option value="1" selected>Prefix (e.g. {{ \App\Helpers\CurrencyHelper::getSymbol() }}100.00)</option>
                                    <option value="2">Suffix (e.g. 100.00{{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="pur_currency_is_active" name="is_active" value="1" checked
                                        style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="pur_currency_is_active">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSavePurCurrency">
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
        if (window.__purCurrencyQuickAddInstalled) return;
        window.__purCurrencyQuickAddInstalled = true;

        const currencySelectId = @json($currencySelectId);

        const modalEl = document.getElementById('purAddCurrencyModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__purCurrencyAlertGuardInstalled) {
            window.__purCurrencyAlertGuardInstalled = true;
            window.__purCurrencyQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__purCurrencyQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__purCurrencyQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloat = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__purCurrencyQuickAddSilent) return;
                    return nativeFloat.apply(window, arguments);
                };
            }
            if (typeof window.showCustomToast === 'function') {
                const nativeCustom = window.showCustomToast;
                window.showCustomToast = function () {
                    if (window.__purCurrencyQuickAddSilent) return;
                    return nativeCustom.apply(window, arguments);
                };
            }
        }

        function purCurMutePopups(on) {
            window.__purCurrencyQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__purCurrencyToastrBackup) {
                    window.__purCurrencyToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__purCurrencyToastrBackup) {
                toastr.success = window.__purCurrencyToastrBackup.success;
                toastr.error = window.__purCurrencyToastrBackup.error;
                toastr.info = window.__purCurrencyToastrBackup.info;
                toastr.warning = window.__purCurrencyToastrBackup.warning;
            }
        }

        function purCurGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function purCurHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = purCurGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function purCurShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = purCurGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function purCurEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function purCurShowFormError(errorId, message, formEl) {
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
                html += '<div>' + purCurEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + purCurEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function purCurClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function purCurMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function purCurExtractErrorPayload(xhr) {
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

        function purCurHandleSaveFail(errorId, formEl, xhr) {
            const payload = purCurExtractErrorPayload(xhr);
            purCurClearFormError(errorId, formEl);
            if (payload.errors) purCurMarkFieldErrors(formEl, payload.errors);
            purCurShowFormError(errorId, payload.messages, formEl);
        }

        function purCurAppendCurrencyOption(selectId, currency, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || !currency || currency.code == null || currency.code === '') return;
            const optValue = String(currency.code);
            const label = currency.name || currency.code;
            const rate = currency.exchange_rate != null ? currency.exchange_rate : 1;

            let opt = null;
            for (let i = 0; i < el.options.length; i++) {
                if (el.options[i].value === optValue) {
                    opt = el.options[i];
                    opt.text = label;
                    break;
                }
            }
            if (!opt) {
                opt = new Option(label, optValue, false, false);
                el.appendChild(opt);
            }
            opt.setAttribute('data-exchange-rate', rate);
            $(opt).data('exchange-rate', rate);

            if (selectIt) {
                el.value = optValue;
                $(el).trigger('change');
            }
        }

        function purCurPostJson(url, data) {
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

        window.openPurCurrencyModal = function () {
            const form = document.getElementById('purAddCurrencyForm');
            if (!form) return;
            form.reset();
            purCurClearFormError('purCurrencyFormError', form);
            const active = document.getElementById('pur_currency_is_active');
            if (active) active.checked = true;
            const rate = document.getElementById('pur_currency_rate');
            if (rate) rate.value = '1';
            const position = document.getElementById('pur_currency_position');
            if (position) position.value = '1';
            purCurShowModal('purAddCurrencyModal');
            setTimeout(function () {
                const nameEl = document.getElementById('pur_currency_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.savePurCurrency = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#purAddCurrencyForm');
            const btn = $('#btnSavePurCurrency');
            if (!form.length) return false;

            purCurClearFormError('purCurrencyFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            purCurMutePopups(true);

            purCurPostJson("{{ route('currencies.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.currency) {
                        const c = response.currency;
                        if (c.is_active === false) {
                            purCurAppendCurrencyOption(currencySelectId, c, false);
                            purCurShowFormError('purCurrencyFormError',
                                @json(__('Currency saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        purCurAppendCurrencyOption(currencySelectId, c, true);
                        purCurHideModal('purAddCurrencyModal');
                        form[0].reset();
                        const active = document.getElementById('pur_currency_is_active');
                        if (active) active.checked = true;
                        const rate = document.getElementById('pur_currency_rate');
                        if (rate) rate.value = '1';
                        return;
                    }
                    purCurShowFormError('purCurrencyFormError',
                        (response && response.message) || @json(__('Could not save currency.')), form[0]);
                })
                .fail(function (xhr) {
                    purCurHandleSaveFail('purCurrencyFormError', form[0], xhr);
                })
                .always(function () {
                    purCurMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
