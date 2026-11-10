{{-- Sale List Ship modal: Courier silent quick-add (Delivery/Quotation pattern) --}}
<style>
    .ship-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .ship-field-group > .form-control,
    .ship-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .ship-field-group > .btn-ship-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .ship-form-error {
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

    .ship-form-error.is-visible {
        display: block;
    }

    .ship-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .ship-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .ship-field-group > .btn-ship-add {
            min-width: 38px;
        }
    }
</style>

@can('manage_api_integrations')
    <div class="modal fade" id="shipAddCourierModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Add Courier</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="shipAddCourierForm" onsubmit="return saveShipCourier(event)" novalidate>
                        @csrf
                        <div class="ship-form-error" id="shipCourierFormError" role="alert"></div>

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
                            <button type="button" class="btn btn-outline-primary btn-sm" id="shipAddParamBtn">
                                <i class="fas fa-plus me-1"></i> Add Row
                            </button>
                        </div>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered table-sm mb-0" id="shipParamsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="shipParamsBody">
                                    <tr>
                                        <td class="text-center align-middle ship-row-num">1</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="params[0][key]" placeholder="e.g., api_key">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="params[0][value]" placeholder="Value">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-outline-danger btn-sm ship-remove-row">
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
                                <input class="form-check-input" type="checkbox" role="switch" id="ship_courier_is_active"
                                    name="is_active" value="1" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="ship_courier_is_active">Yes</label>
                            </div>
                            <small class="text-muted">Inactive couriers will not be selected for shipment.</small>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveShipCourier">
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
        if (window.__shipCourierQuickAddInstalled) return;
        window.__shipCourierQuickAddInstalled = true;

        let shipParamRowCount = 1;
        const shipParentModalId = 'shippingModal';
        let shipPausedParent = false;
        let shipResumeScheduled = false;

        const modalEl = document.getElementById('shipAddCourierModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__shipCourierAlertGuardInstalled) {
            window.__shipCourierAlertGuardInstalled = true;
            window.__shipCourierSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__shipCourierSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__shipCourierSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }

            if (typeof window.showCustomToast === 'function') {
                const nativeCustom = window.showCustomToast;
                window.showCustomToast = function () {
                    if (window.__shipCourierSilent) return;
                    return nativeCustom.apply(window, arguments);
                };
            }
        }

        function shipMutePopups(on) {
            window.__shipCourierSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__shipToastrBackup) {
                    window.__shipToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__shipToastrBackup) {
                toastr.success = window.__shipToastrBackup.success;
                toastr.error = window.__shipToastrBackup.error;
                toastr.info = window.__shipToastrBackup.info;
                toastr.warning = window.__shipToastrBackup.warning;
            }
        }

        function shipGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function shipHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === shipParentModalId && window.shippingModalInstance) {
                window.shippingModalInstance.hide();
                return;
            }
            const bs = shipGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function shipShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === shipParentModalId && window.shippingModalInstance) {
                window.shippingModalInstance.show();
                return;
            }
            const bs = shipGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function shipClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function shipResumeParentModal() {
            if (!shipPausedParent || shipResumeScheduled) return;
            shipPausedParent = false;
            shipResumeScheduled = true;
            setTimeout(function () {
                shipResumeScheduled = false;
                shipClearStrayBackdrops();
                shipShowModal(shipParentModalId);
            }, 200);
        }

        function shipOpenOverParent(quickModalId) {
            const parentEl = document.getElementById(shipParentModalId);
            let opened = false;
            const openQuick = function () {
                if (opened) return;
                opened = true;
                shipShowModal(quickModalId);
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                (typeof $ !== 'undefined' && $(parentEl).is(':visible'))
            );
            if (parentVisible) {
                shipPausedParent = true;
                shipResumeScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                shipHideModal(shipParentModalId);
                setTimeout(function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                }, 450);
            } else {
                shipPausedParent = false;
                openQuick();
            }
        }

        if (modalEl && !modalEl.dataset.shipResumeBound) {
            modalEl.dataset.shipResumeBound = '1';
            modalEl.addEventListener('hidden.bs.modal', function () {
                shipResumeParentModal();
            });
        }

        const shipParentEl = document.getElementById(shipParentModalId);
        if (shipParentEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            window.shippingModalInstance = bootstrap.Modal.getOrCreateInstance(shipParentEl);
        }

        function shipUpdateParamRowNumbers() {
            $('#shipParamsBody tr').each(function (i) {
                $(this).find('.ship-row-num').text(i + 1);
            });
        }

        function shipResetParamsTable() {
            shipParamRowCount = 1;
            const tbody = document.getElementById('shipParamsBody');
            if (!tbody) return;
            tbody.innerHTML = `
                <tr>
                    <td class="text-center align-middle ship-row-num">1</td>
                    <td><input type="text" class="form-control form-control-sm" name="params[0][key]" placeholder="e.g., api_key"></td>
                    <td><input type="text" class="form-control form-control-sm" name="params[0][value]" placeholder="Value"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-outline-danger btn-sm ship-remove-row"><i class="fas fa-times"></i></button>
                    </td>
                </tr>`;
        }

        $(document).off('click.shipCgParam', '#shipAddParamBtn').on('click.shipCgParam', '#shipAddParamBtn', function () {
            const tbody = document.getElementById('shipParamsBody');
            if (!tbody) return;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-center align-middle ship-row-num">${shipParamRowCount + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="params[${shipParamRowCount}][key]" placeholder="Parameter"></td>
                <td><input type="text" class="form-control form-control-sm" name="params[${shipParamRowCount}][value]" placeholder="Value"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm ship-remove-row"><i class="fas fa-times"></i></button>
                </td>`;
            tbody.appendChild(row);
            shipParamRowCount++;
            shipUpdateParamRowNumbers();
        });

        $(document).off('click.shipCgRemove', '#shipParamsBody .ship-remove-row')
            .on('click.shipCgRemove', '#shipParamsBody .ship-remove-row', function () {
                $(this).closest('tr').remove();
                shipUpdateParamRowNumbers();
            });

        function shipEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function shipShowFormError(errorId, message, formEl) {
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
                '<div class="ship-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + shipEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + shipEscapeHtml(m) + '</li>';
                });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try {
                    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (e) {}
            }
        }

        function shipClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function shipMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function shipExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (e) {
                    payload = null;
                }
            }
            if (xhr.status === 419) {
                return { messages: [@json(__('Your session has expired. Please refresh the page and try again.'))], errors: null };
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
                return { messages: messages.length ? messages : [@json(__('Validation failed.'))], errors: payload.errors };
            }
            if (payload && payload.message) {
                return { messages: [payload.message], errors: payload.errors || null };
            }
            return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
        }

        function shipHandleSaveFail(errorId, formEl, xhr) {
            const payload = shipExtractErrorPayload(xhr);
            shipClearFormError(errorId, formEl);
            if (payload.errors) shipMarkFieldErrors(formEl, payload.errors);
            shipShowFormError(errorId, payload.messages, formEl);
        }

        function shipAppendCourierOption(id, name, charge, selectIt) {
            const el = document.getElementById('ship_courier');
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
            if (typeof courierCharges === 'object') {
                courierCharges[value] = parseFloat(charge) || 0;
            }
            if (typeof window.courierCharges === 'object') {
                window.courierCharges[value] = parseFloat(charge) || 0;
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
                if (typeof window.updateDeliveryCharge === 'function') {
                    window.updateDeliveryCharge();
                }
            }
        }

        window.openShipCourierModal = function () {
            const form = document.getElementById('shipAddCourierForm');
            if (!form) return;
            form.reset();
            shipClearFormError('shipCourierFormError', form);
            shipResetParamsTable();
            const active = document.getElementById('ship_courier_is_active');
            if (active) active.checked = true;
            const charge = form.querySelector('[name="default_charge"]');
            if (charge) charge.value = '0';
            shipOpenOverParent('shipAddCourierModal');
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveShipCourier = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#shipAddCourierForm');
            const btn = $('#btnSaveShipCourier');
            if (!form.length) return false;

            shipClearFormError('shipCourierFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            shipMutePopups(true);

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
                            shipShowFormError('shipCourierFormError',
                                @json(__('Courier saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            shipResetParamsTable();
                            const active = document.getElementById('ship_courier_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        shipAppendCourierOption(c.id, c.name, c.default_charge, true);
                        shipHideModal('shipAddCourierModal');
                        form[0].reset();
                        shipResetParamsTable();
                        const active = document.getElementById('ship_courier_is_active');
                        if (active) active.checked = true;
                        shipClearFormError('shipCourierFormError', form[0]);
                        return;
                    }
                    shipShowFormError('shipCourierFormError',
                        (response && response.message) || @json(__('Could not save courier.')), form[0]);
                })
                .fail(function (xhr) {
                    shipHandleSaveFail('shipCourierFormError', form[0], xhr);
                })
                .always(function () {
                    shipMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
