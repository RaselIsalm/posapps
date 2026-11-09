{{-- Delivery Edit: Courier quick-add (silent AJAX, full create fields) --}}
<style>
    .del-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .del-field-group > .form-control,
    .del-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .del-field-group > .btn-del-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .del-form-error {
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

    .del-form-error.is-visible {
        display: block;
    }

    .del-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .del-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .del-field-group > .btn-del-add {
            min-width: 38px;
        }
    }
</style>

@can('manage_api_integrations')
    <div class="modal fade del-quick-modal" id="delAddCourierModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Courier</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="delAddCourierForm" class="del-quick-form" onsubmit="return saveDelCourier(event)"
                        novalidate>
                        @csrf
                        <div class="del-form-error" id="delCourierFormError" role="alert"></div>

                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Provider Name *</label>
                            <input type="text" name="name" class="form-control" required
                                placeholder="e.g., Stead Fast, Pathao">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Phone Number *</label>
                            <input type="text" name="phone" class="form-control" required
                                placeholder="e.g., 01700000000">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Base URL <span
                                    class="fw-normal">(Optional)</span></label>
                            <input type="url" name="base_url" class="form-control"
                                placeholder="https://example.com/api/v1">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Default Delivery Charge</label>
                            <input type="number" name="default_charge" class="form-control" step="0.01" min="0"
                                value="0" placeholder="0.00">
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2 mt-3">
                            <label class="small fw-bold text-muted mb-0">Courier Configuration Parameters</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="delAddParamBtn">
                                <i class="fas fa-plus me-1"></i> Add Row
                            </button>
                        </div>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered table-sm mb-0" id="delParamsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="delParamsBody">
                                    <tr>
                                        <td class="text-center align-middle del-row-num">1</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="params[0][key]" placeholder="e.g., api_key">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="params[0][value]" placeholder="Value">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-outline-danger btn-sm del-remove-row">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Address *</label>
                            <input type="text" name="address" class="form-control" required
                                placeholder="Enter Address">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="del_courier_is_active"
                                    name="is_active" value="1" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="del_courier_is_active">Yes</label>
                            </div>
                            <small class="text-muted">Inactive couriers will not be selected for delivery.</small>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveDelCourier">
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
        if (window.__delCourierQuickAddInstalled) return;
        window.__delCourierQuickAddInstalled = true;

        let delParamRowCount = 1;
        const delParentModalId = 'editModal';

        const modalEl = document.getElementById('delAddCourierModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__delCourierAlertGuardInstalled) {
            window.__delCourierAlertGuardInstalled = true;
            window.__delCourierSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__delCourierSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__delCourierSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        }

        function delMutePopups(on) {
            window.__delCourierSilent = !!on;

            if (typeof window.showFloatingAlert === 'function' && !window.__delFloatingWrapped) {
                window.__delFloatingWrapped = true;
                const nativeFloating = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__delCourierSilent) return;
                    return nativeFloating.apply(window, arguments);
                };
            }

            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__delToastrBackup) {
                    window.__delToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__delToastrBackup) {
                toastr.success = window.__delToastrBackup.success;
                toastr.error = window.__delToastrBackup.error;
                toastr.info = window.__delToastrBackup.info;
                toastr.warning = window.__delToastrBackup.warning;
            }
        }

        function delGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function delHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === delParentModalId && window.deliveryEditModalInstance) {
                window.deliveryEditModalInstance.hide();
                return;
            }
            const bs = delGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function delShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === delParentModalId && window.deliveryEditModalInstance) {
                window.deliveryEditModalInstance.show();
                return;
            }
            const bs = delGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function delClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        let delPausedParent = false;
        let delResumeScheduled = false;

        function delResumeParentModal() {
            if (!delPausedParent || delResumeScheduled) return;
            delPausedParent = false;
            delResumeScheduled = true;
            setTimeout(function () {
                delResumeScheduled = false;
                delClearStrayBackdrops();
                delShowModal(delParentModalId);
            }, 200);
        }

        function delOpenOverParent(quickModalId) {
            const parentEl = document.getElementById(delParentModalId);
            let opened = false;
            const openQuick = function () {
                if (opened) return;
                opened = true;
                delShowModal(quickModalId);
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                (typeof $ !== 'undefined' && $(parentEl).is(':visible'))
            );
            if (parentVisible) {
                delPausedParent = true;
                delResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                delHideModal(delParentModalId);
                // Fallback if Bootstrap hide event does not fire
                setTimeout(function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                }, 450);
            } else {
                delPausedParent = false;
                openQuick();
            }
        }

        if (modalEl && !modalEl.dataset.delResumeBound) {
            modalEl.dataset.delResumeBound = '1';
            modalEl.addEventListener('hidden.bs.modal', function () {
                delResumeParentModal();
            });
        }

        function delUpdateParamRowNumbers() {
            $('#delParamsBody tr').each(function (i) {
                $(this).find('.del-row-num').text(i + 1);
            });
        }

        function delResetParamsTable() {
            delParamRowCount = 1;
            const tbody = document.getElementById('delParamsBody');
            if (!tbody) return;
            tbody.innerHTML = `
                <tr>
                    <td class="text-center align-middle del-row-num">1</td>
                    <td><input type="text" class="form-control form-control-sm" name="params[0][key]" placeholder="e.g., api_key"></td>
                    <td><input type="text" class="form-control form-control-sm" name="params[0][value]" placeholder="Value"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-outline-danger btn-sm del-remove-row"><i class="fas fa-times"></i></button>
                    </td>
                </tr>`;
        }

        $(document).off('click.delCgParam', '#delAddParamBtn').on('click.delCgParam', '#delAddParamBtn', function () {
            const tbody = document.getElementById('delParamsBody');
            if (!tbody) return;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-center align-middle del-row-num">${delParamRowCount + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="params[${delParamRowCount}][key]" placeholder="Parameter"></td>
                <td><input type="text" class="form-control form-control-sm" name="params[${delParamRowCount}][value]" placeholder="Value"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm del-remove-row"><i class="fas fa-times"></i></button>
                </td>`;
            tbody.appendChild(row);
            delParamRowCount++;
            delUpdateParamRowNumbers();
        });

        $(document).off('click.delCgRemove', '#delParamsBody .del-remove-row')
            .on('click.delCgRemove', '#delParamsBody .del-remove-row', function () {
                $(this).closest('tr').remove();
                delUpdateParamRowNumbers();
            });

        function delEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function delShowFormError(errorId, message, formEl) {
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
                '<div class="del-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + delEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + delEscapeHtml(m) + '</li>';
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

        function delClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function delMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function delExtractErrorPayload(xhr) {
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

        function delHandleSaveFail(errorId, formEl, xhr) {
            const payload = delExtractErrorPayload(xhr);
            delClearFormError(errorId, formEl);
            if (payload.errors) delMarkFieldErrors(formEl, payload.errors);
            delShowFormError(errorId, payload.messages, formEl);
        }

        function delAppendCourierOption(id, name, charge, selectIt) {
            const el = document.getElementById('edit_courier_id');
            if (!el || id == null) return;
            const value = String(id);
            let exists = false;
            for (let i = 0; i < el.options.length; i++) {
                if (el.options[i].value === value) {
                    exists = true;
                    el.options[i].text = name;
                    break;
                }
            }
            if (!exists) {
                el.appendChild(new Option(name, value, false, false));
            }
            if (typeof window.editCourierCharges === 'object') {
                window.editCourierCharges[value] = parseFloat(charge) || 0;
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }

            // Also refresh filter dropdown if present
            const filter = document.getElementById('filter_courier');
            if (filter) {
                let fExists = false;
                for (let i = 0; i < filter.options.length; i++) {
                    if (filter.options[i].value === value) {
                        fExists = true;
                        break;
                    }
                }
                if (!fExists) {
                    filter.appendChild(new Option(name, value, false, false));
                }
            }
        }

        window.openDelCourierModal = function () {
            const form = document.getElementById('delAddCourierForm');
            if (!form) return;
            form.reset();
            delClearFormError('delCourierFormError', form);
            delResetParamsTable();
            const active = document.getElementById('del_courier_is_active');
            if (active) active.checked = true;
            const charge = form.querySelector('[name="default_charge"]');
            if (charge) charge.value = '0';
            delOpenOverParent('delAddCourierModal');
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveDelCourier = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#delAddCourierForm');
            const btn = $('#btnSaveDelCourier');
            if (!form.length) return false;

            delClearFormError('delCourierFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            delMutePopups(true);

            $.ajax({
                url: "{{ route('courier_settings.store') }}",
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .done(function (response) {
                    if (response && response.success && response.courier) {
                        const c = response.courier;
                        if (c.is_active === false) {
                            delShowFormError('delCourierFormError',
                                @json(__('Courier saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            delResetParamsTable();
                            const active = document.getElementById('del_courier_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        delAppendCourierOption(c.id, c.name, c.default_charge, true);
                        delHideModal('delAddCourierModal');
                        form[0].reset();
                        delResetParamsTable();
                        const active = document.getElementById('del_courier_is_active');
                        if (active) active.checked = true;
                        delClearFormError('delCourierFormError', form[0]);
                        return;
                    }
                    delShowFormError('delCourierFormError',
                        (response && response.message) || @json(__('Could not save courier.')), form[0]);
                })
                .fail(function (xhr) {
                    delHandleSaveFail('delCourierFormError', form[0], xhr);
                })
                .always(function () {
                    delMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
