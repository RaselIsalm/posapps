<style>
    .gc-field-group .btn-gc-add {
        min-width: 42px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .gc-field-group:has(.btn-gc-add) .form-select,
    .gc-field-group:has(.btn-gc-add) .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .gc-form-error {
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

    .gc-form-error.is-visible {
        display: block;
    }

    .gc-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .gc-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .gc-quick-modal {
        z-index: 1065 !important;
    }

    .modal-backdrop.gc-quick-backdrop {
        z-index: 1060 !important;
    }

    @media (max-width: 575.98px) {
        .gc-field-group .btn-gc-add {
            min-width: 38px;
        }
    }
</style>

{{-- Quick Add Customer --}}
@can('create_customers')
    <div class="modal fade gc-quick-modal" id="gcAddCustomerModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="gcAddCustomerForm" onsubmit="return saveGcCustomer(event)">
                        @csrf
                        <div class="gc-form-error" id="gcCustomerFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Phone *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Customer Group</label>
                            <div class="input-group gc-field-group">
                                <select name="customer_group_id" id="gc_customer_group_id" class="form-control">
                                    <option value="">Select Group</option>
                                    @foreach ($customer_groups ?? [] as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @can('create_customer_groups')
                                    <button type="button" class="btn btn-outline-primary btn-gc-add"
                                        onclick="openGcCustomerGroupModal()" title="Add Customer Group">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Previous Due</label>
                            <input type="number" name="previous_due" class="form-control" step="any" min="0"
                                placeholder="0.00" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="gc_customer_status"
                                    name="status" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="gc_customer_status">Yes</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveGcCustomer">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Quick Add Customer Group (nested over Add Customer) --}}
@can('create_customer_groups')
    <div class="modal fade gc-quick-modal" id="gcAddCustomerGroupModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-users me-2"></i>Add Customer Group</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="gcAddCustomerGroupForm" onsubmit="return saveGcCustomerGroup(event)">
                        @csrf
                        <div class="gc-form-error" id="gcCustomerGroupFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="Customer Group Name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Prefix Code *</label>
                                <input type="text" name="prefix_code" class="form-control" required
                                    placeholder="Prefix Code">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Discount</label>
                                <input type="number" step="0.01" name="discount" class="form-control" value="0"
                                    required placeholder="0">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Discount Type</label>
                                <select name="type" class="form-control">
                                    <option value="percentage" selected>Percentage</option>
                                    <option value="fixed">Fixed ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="small fw-bold text-muted mb-1">Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Optional description"></textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="gc_cg_status"
                                        name="status" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="gc_cg_status">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveGcCustomerGroup">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Quick Add Account / Payment Method --}}
@can('create_accounts')
    <div class="modal fade gc-quick-modal" id="gcAddAccountModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="gcAddAccountForm" onsubmit="return saveGcAccount(event)">
                        @csrf
                        <div class="gc-form-error" id="gcAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Account name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Type *</label>
                                <select name="type" id="gc_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">
                                    Account Number <span id="gc_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="gc_account_number" class="form-control"
                                    placeholder="Not required for cash">
                                <small id="gc_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="gc_account_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="gc_account_is_active">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="gc_account_is_default"
                                        name="is_default" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="gc_account_is_default">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="gc_account_manual"
                                        name="allow_manual_amount" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="gc_account_manual">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveGcAccount">
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
        let gcCustomerTargetSelect = '#gc_customer_id';
        let gcAccountTargetSelect = '#gc_payment_method';
        let gcPausedParentModalId = null;
        let gcResumeScheduled = false;
        let gcPausedCustomerForGroup = false;
        let gcResumeCustomerScheduled = false;

        ['gcAddCustomerModal', 'gcAddCustomerGroupModal', 'gcAddAccountModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        // Hard rule: Gift Card quick-add must NEVER show alert / toastr / toast.
        if (!window.__gcAlertGuardInstalled) {
            window.__gcAlertGuardInstalled = true;
            window.__gcQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__gcQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__gcQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }

            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__gcQuickAddSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }
        }

        function gcMutePopups(on) {
            window.__gcQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__gcToastrBackup) {
                    window.__gcToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__gcToastrBackup) {
                toastr.success = window.__gcToastrBackup.success;
                toastr.error = window.__gcToastrBackup.error;
                toastr.info = window.__gcToastrBackup.info;
                toastr.warning = window.__gcToastrBackup.warning;
            }
        }

        function gcGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function gcHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = gcGetBsModal(el);
            if (bs) {
                bs.hide();
            } else {
                $('#' + id).modal('hide');
            }
        }

        function gcShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;

            // Prefer the same BS instance Sell Gift Card already uses
            if (id === 'giftCardModal' && window.giftCardModalInstance) {
                window.giftCardModalInstance.show();
                return;
            }

            const bs = gcGetBsModal(el);
            if (bs) {
                bs.show();
            } else {
                $('#' + id).modal('show');
            }
        }

        function gcResolveParentModalId(selectSelector) {
            const s = String(selectSelector || '');
            if (s.indexOf('editGiftCardForm') !== -1) return 'editModal';
            if (s.indexOf('rechargeGiftCardForm') !== -1) return 'rechargeModal';
            return 'giftCardModal';
        }

        function gcClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function gcResumeParentModal() {
            if (!gcPausedParentModalId || gcResumeScheduled) return;
            const parentId = gcPausedParentModalId;
            gcPausedParentModalId = null;
            gcResumeScheduled = true;

            setTimeout(function () {
                gcResumeScheduled = false;
                gcClearStrayBackdrops();
                gcShowModal(parentId);
            }, 200);
        }

        function gcResumeCustomerModal() {
            if (!gcPausedCustomerForGroup || gcResumeCustomerScheduled) return;
            gcPausedCustomerForGroup = false;
            gcResumeCustomerScheduled = true;
            setTimeout(function () {
                gcResumeCustomerScheduled = false;
                gcClearStrayBackdrops();
                gcShowModal('gcAddCustomerModal');
            }, 200);
        }

        function gcOpenQuickOverParent(quickModalId, selectSelector) {
            const parentId = gcResolveParentModalId(selectSelector);
            const parentEl = document.getElementById(parentId);

            const openQuick = function () {
                // Always use Bootstrap Modal API (same as data-bs-dismiss Cancel)
                gcShowModal(quickModalId);
            };

            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                $(parentEl).is(':visible')
            );

            if (parentVisible) {
                gcPausedParentModalId = parentId;
                gcResumeScheduled = false;

                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                gcHideModal(parentId);
            } else {
                gcPausedParentModalId = null;
                openQuick();
            }
        }

        function gcOpenGroupOverCustomer() {
            const customerEl = document.getElementById('gcAddCustomerModal');
            const openGroup = function () {
                gcShowModal('gcAddCustomerGroupModal');
            };

            const customerVisible = customerEl && (
                customerEl.classList.contains('show') ||
                $(customerEl).is(':visible')
            );

            if (customerVisible) {
                gcPausedCustomerForGroup = true;
                gcResumeCustomerScheduled = false;
                const onHidden = function () {
                    customerEl.removeEventListener('hidden.bs.modal', onHidden);
                    openGroup();
                };
                customerEl.addEventListener('hidden.bs.modal', onHidden);
                gcHideModal('gcAddCustomerModal');
            } else {
                gcPausedCustomerForGroup = false;
                openGroup();
            }
        }

        // Resume Sell/Edit/Recharge when Customer or Payment Method modal fully closes
        // (skip resume when Customer is only paused for nested Customer Group)
        const gcCustomerModalEl = document.getElementById('gcAddCustomerModal');
        if (gcCustomerModalEl) {
            gcCustomerModalEl.addEventListener('hidden.bs.modal', function () {
                if (gcPausedCustomerForGroup) return;
                gcResumeParentModal();
            });
        }
        const gcAccountModalEl = document.getElementById('gcAddAccountModal');
        if (gcAccountModalEl) {
            gcAccountModalEl.addEventListener('hidden.bs.modal', function () {
                gcResumeParentModal();
            });
        }
        const gcGroupModalEl = document.getElementById('gcAddCustomerGroupModal');
        if (gcGroupModalEl) {
            gcGroupModalEl.addEventListener('hidden.bs.modal', function () {
                gcResumeCustomerModal();
            });
        }

        function gcEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function gcShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="gc-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + gcEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + gcEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function gcClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.gc-field-error').remove();
            }
        }

        function gcMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block gc-field-error';
                feedback.textContent = msg;
                input.insertAdjacentElement('afterend', feedback);
            });
        }

        function gcExtractErrorPayload(xhr) {
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

        function gcHandleSaveFail(errorId, formEl, xhr) {
            const payload = gcExtractErrorPayload(xhr);
            gcClearFormError(errorId, formEl);
            if (payload.errors) gcMarkFieldErrors(formEl, payload.errors);
            gcShowFormError(errorId, payload.messages, formEl);
        }

        function gcAppendOption(selector, id, text, selectIt) {
            const el = document.querySelector(selector);
            if (!el || id == null) return;
            const value = String(id);
            const label = text || ('#' + value);
            if ($(el).find('option[value="' + value + '"]').length === 0) {
                el.appendChild(new Option(label, value, false, false));
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }
        }

        function gcSyncCustomerOption(id, text, preferredSelector) {
            const selectors = ['#gc_customer_id', '#editGiftCardForm select[name="customer_id"]', '#customerSelect'];
            const preferred = preferredSelector || gcCustomerTargetSelect;
            let preferredHandled = false;
            selectors.forEach(function (sel) {
                const isPreferred = sel === preferred;
                if (isPreferred) preferredHandled = true;
                gcAppendOption(sel, id, text, isPreferred);
            });
            if (!preferredHandled && preferred) {
                gcAppendOption(preferred, id, text, true);
            }
        }

        function gcSyncAccountOption(id, text, preferredSelector) {
            const selectors = ['#gc_payment_method', '#rechargeGiftCardForm select[name="account_id"]'];
            const preferred = preferredSelector || gcAccountTargetSelect;
            let preferredHandled = false;
            selectors.forEach(function (sel) {
                const isPreferred = sel === preferred;
                if (isPreferred) preferredHandled = true;
                gcAppendOption(sel, id, text, isPreferred);
            });
            if (!preferredHandled && preferred) {
                gcAppendOption(preferred, id, text, true);
            }
        }

        function gcPostJson(url, data) {
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

        function syncGcAccountNumberRequirement() {
            const typeEl = document.getElementById('gc_account_type');
            const numberEl = document.getElementById('gc_account_number');
            const requiredMark = document.getElementById('gc_account_number_required');
            const hintEl = document.getElementById('gc_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.openGcCustomerModal = function (selectSelector) {
            const form = document.getElementById('gcAddCustomerForm');
            if (!form) return;
            gcCustomerTargetSelect = selectSelector || '#gc_customer_id';
            form.reset();
            gcClearFormError('gcCustomerFormError', form);
            const active = document.getElementById('gc_customer_status');
            if (active) active.checked = true;
            const due = form.querySelector('[name="previous_due"]');
            if (due) due.value = '0';
            gcOpenQuickOverParent('gcAddCustomerModal', gcCustomerTargetSelect);
        };

        window.openGcCustomerGroupModal = function () {
            const form = document.getElementById('gcAddCustomerGroupForm');
            if (!form) return;
            form.reset();
            gcClearFormError('gcCustomerGroupFormError', form);
            const active = document.getElementById('gc_cg_status');
            if (active) active.checked = true;
            const discount = form.querySelector('[name="discount"]');
            if (discount) discount.value = '0';
            const type = form.querySelector('[name="type"]');
            if (type) type.value = 'percentage';
            gcOpenGroupOverCustomer();
            setTimeout(function () {
                const name = form.querySelector('[name="name"]');
                if (name) name.focus();
            }, 350);
        };

        window.openGcAccountModal = function (selectSelector) {
            const form = document.getElementById('gcAddAccountForm');
            if (!form) return;
            gcAccountTargetSelect = selectSelector || '#gc_payment_method';
            form.reset();
            gcClearFormError('gcAccountFormError', form);
            const active = document.getElementById('gc_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('gc_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncGcAccountNumberRequirement();
            gcOpenQuickOverParent('gcAddAccountModal', gcAccountTargetSelect);
        };

        window.saveGcCustomer = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#gcAddCustomerForm');
            const btn = $('#btnSaveGcCustomer');
            if (!form.length) return false;

            gcClearFormError('gcCustomerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            gcMutePopups(true);

            let payload = form.serialize();
            if (!document.getElementById('gc_customer_status')?.checked) {
                payload += (payload ? '&' : '') + 'status=0';
            }

            gcPostJson("{{ route('customers.store') }}", payload)
                .done(function (response) {
                    if (response && response.success && response.customer) {
                        const c = response.customer;
                        if (!c.status) {
                            gcShowFormError('gcCustomerFormError',
                                @json(__('Customer saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        const label = (c.name || 'Customer') + (c.phone ? ' (' + c.phone + ')' : '');
                        gcSyncCustomerOption(c.id, label, gcCustomerTargetSelect);
                        gcHideModal('gcAddCustomerModal');
                        form[0].reset();
                        const active = document.getElementById('gc_customer_status');
                        if (active) active.checked = true;
                        return;
                    }
                    gcShowFormError('gcCustomerFormError',
                        (response && response.message) || @json(__('Could not save customer.')), form[0]);
                })
                .fail(function (xhr) {
                    gcHandleSaveFail('gcCustomerFormError', form[0], xhr);
                })
                .always(function () {
                    gcMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveGcCustomerGroup = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#gcAddCustomerGroupForm');
            const btn = $('#btnSaveGcCustomerGroup');
            if (!form.length) return false;

            gcClearFormError('gcCustomerGroupFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            gcMutePopups(true);

            const data = form.serializeArray();
            if (!$('#gc_cg_status').is(':checked')) {
                data.push({ name: 'status', value: '0' });
            }

            gcPostJson("{{ route('customer-groups.store') }}", $.param(data))
                .done(function (response) {
                    if (response && response.success && response.customer_group) {
                        const g = response.customer_group;
                        if (g.status === false) {
                            gcShowFormError('gcCustomerGroupFormError',
                                @json(__('Customer group saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        gcAppendOption('#gc_customer_group_id', g.id, g.name, true);
                        gcHideModal('gcAddCustomerGroupModal');
                        form[0].reset();
                        const active = document.getElementById('gc_cg_status');
                        if (active) active.checked = true;
                        return;
                    }
                    gcShowFormError('gcCustomerGroupFormError',
                        (response && response.message) || @json(__('Could not save customer group.')), form[0]);
                })
                .fail(function (xhr) {
                    gcHandleSaveFail('gcCustomerGroupFormError', form[0], xhr);
                })
                .always(function () {
                    gcMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveGcAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#gcAddAccountForm');
            const btn = $('#btnSaveGcAccount');
            if (!form.length) return false;

            gcClearFormError('gcAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            gcMutePopups(true);

            gcPostJson("{{ route('accounts.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            gcShowFormError('gcAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        gcSyncAccountOption(a.id, a.name, gcAccountTargetSelect);
                        if (typeof qsSyncAccountToSelects === 'function') {
                            qsSyncAccountToSelects(a, null);
                        }
                        gcHideModal('gcAddAccountModal');
                        form[0].reset();
                        const active = document.getElementById('gc_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    gcShowFormError('gcAccountFormError',
                        (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    gcHandleSaveFail('gcAccountFormError', form[0], xhr);
                })
                .always(function () {
                    gcMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('change.gcAccType', '#gc_account_type')
            .on('change.gcAccType', '#gc_account_type', syncGcAccountNumberRequirement);
    })();
</script>
@endpush
