{{-- Customer Edit: Customer Group quick-add (silent AJAX, no toaster / reload) --}}

<style>
    .cg-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .cg-field-group > .form-control,
    .cg-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .cg-field-group > .btn-cg-add {
        min-width: 42px;
        height: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .cg-form-error {
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

    .cg-form-error.is-visible {
        display: block;
    }

    .cg-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .cg-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .cg-field-group > .btn-cg-add {
            min-width: 38px;
        }
    }
</style>

@can('create_customer_groups')
    <div class="modal fade" id="custEditCgModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-users me-2"></i>Add Customer Group</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="custEditCgForm" onsubmit="return saveCustEditCustomerGroup(event)">
                        @csrf
                        <div class="cg-form-error" id="custEditCgFormError" role="alert"></div>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="cust_edit_cg_status"
                                        name="status" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="cust_edit_cg_status">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveCustEditCg">
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
        if (window.__custEditCgQuickAddInstalled) return;
        window.__custEditCgQuickAddInstalled = true;

        const modalEl = document.getElementById('custEditCgModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__custEditCgAlertGuardInstalled) {
            window.__custEditCgAlertGuardInstalled = true;
            window.__custEditCgSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__custEditCgSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__custEditCgSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }

            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__custEditCgSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }
        }

        function cgMutePopups(on) {
            window.__custEditCgSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__custEditCgToastrBackup) {
                    window.__custEditCgToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__custEditCgToastrBackup) {
                toastr.success = window.__custEditCgToastrBackup.success;
                toastr.error = window.__custEditCgToastrBackup.error;
                toastr.info = window.__custEditCgToastrBackup.info;
                toastr.warning = window.__custEditCgToastrBackup.warning;
            }
        }

        function cgEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function cgShowFormError(errorId, message, formEl) {
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
                '<div class="cg-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + cgEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + cgEscapeHtml(m) + '</li>';
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

        function cgClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function cgMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function cgExtractErrorPayload(xhr) {
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

        function cgHandleSaveFail(errorId, formEl, xhr) {
            const payload = cgExtractErrorPayload(xhr);
            cgClearFormError(errorId, formEl);
            if (payload.errors) cgMarkFieldErrors(formEl, payload.errors);
            cgShowFormError(errorId, payload.messages, formEl);
        }

        window.openCustEditCustomerGroupModal = function () {
            const form = document.getElementById('custEditCgForm');
            if (!form) return;
            form.reset();
            cgClearFormError('custEditCgFormError', form);
            const active = document.getElementById('cust_edit_cg_status');
            if (active) active.checked = true;
            const discount = form.querySelector('[name="discount"]');
            if (discount) discount.value = '0';
            const type = form.querySelector('[name="type"]');
            if (type) type.value = 'percentage';
            $('#custEditCgModal').modal('show');
            setTimeout(function () {
                const name = form.querySelector('[name="name"]');
                if (name) name.focus();
            }, 300);
        };

        window.saveCustEditCustomerGroup = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#custEditCgForm');
            const btn = $('#btnSaveCustEditCg');
            if (!form.length) return false;

            cgClearFormError('custEditCgFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            cgMutePopups(true);

            const data = form.serializeArray();
            // Unchecked checkbox is omitted; ensure inactive is sent as empty so backend treats as off
            if (!$('#cust_edit_cg_status').is(':checked')) {
                data.push({
                    name: 'status',
                    value: '0'
                });
            }

            $.ajax({
                url: "{{ route('customer-groups.store') }}",
                type: 'POST',
                data: $.param(data),
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .done(function (response) {
                    if (response && response.success && response.customer_group) {
                        const g = response.customer_group;
                        if (g.status === false) {
                            cgShowFormError('custEditCgFormError',
                                @json(__('Customer group saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('cust_edit_cg_status');
                            if (active) active.checked = true;
                            return;
                        }

                        const select = document.getElementById('customer_group_id');
                        if (select) {
                            const value = String(g.id);
                            let exists = false;
                            for (let i = 0; i < select.options.length; i++) {
                                if (select.options[i].value === value) {
                                    exists = true;
                                    break;
                                }
                            }
                            if (!exists) {
                                select.appendChild(new Option(g.name, value, true, true));
                            } else {
                                select.value = value;
                            }
                            $(select).trigger('change');
                        }

                        $('#custEditCgModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('cust_edit_cg_status');
                        if (active) active.checked = true;
                        cgClearFormError('custEditCgFormError', form[0]);
                        return;
                    }
                    cgShowFormError('custEditCgFormError',
                        (response && response.message) || @json(__('Could not save customer group.')), form[0]);
                })
                .fail(function (xhr) {
                    cgHandleSaveFail('custEditCgFormError', form[0], xhr);
                })
                .always(function () {
                    cgMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
