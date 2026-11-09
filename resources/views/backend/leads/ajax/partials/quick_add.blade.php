{{-- Leads Add/Edit: Lead Status + Lead Source quick-add (silent AJAX, teal header) --}}
<style>
    .lead-qa-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .lead-qa-field-group > .form-control,
    .lead-qa-field-group > .form-select {
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
    }

    .lead-qa-field-group .btn-lead-qa-add {
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

    .lead-qa-field-group:has(.btn-lead-qa-add) > .form-control,
    .lead-qa-field-group:has(.btn-lead-qa-add) > .form-select {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .lead-qa-form-error {
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

    .lead-qa-form-error.is-visible {
        display: block;
    }

    .lead-qa-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .lead-qa-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .lead-qa-field-group .btn-lead-qa-add {
            min-width: 38px;
            width: 38px;
            flex-basis: 38px;
        }
    }
</style>

@can('create_lead_status')
<div class="modal fade" id="leadAddStatusModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-flag me-2"></i>Add Lead Status</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="leadAddStatusForm" onsubmit="return saveLeadStatus(event)" novalidate>
                    @csrf
                    <div class="lead-qa-form-error" id="leadStatusFormError" role="alert"></div>
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="lead_qa_status_name">Status Name *</label>
                            <input type="text" name="name" id="lead_qa_status_name" class="form-control" required
                                placeholder="e.g. New, Contacted" autocomplete="off">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="lead_qa_status_progress">Progress (%)</label>
                            <input type="number" name="progress" id="lead_qa_status_progress" class="form-control"
                                placeholder="0-100" min="0" max="100" value="0">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Is Active?</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="lead_qa_status_active"
                                    name="status" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="lead_qa_status_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveLeadStatus">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

@can('create_lead_source')
<div class="modal fade" id="leadAddSourceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-share-alt me-2"></i>Add Lead Source</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="leadAddSourceForm" onsubmit="return saveLeadSource(event)" novalidate>
                    @csrf
                    <div class="lead-qa-form-error" id="leadSourceFormError" role="alert"></div>
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1" for="lead_qa_source_name">Source Name *</label>
                            <input type="text" name="name" id="lead_qa_source_name" class="form-control" required
                                placeholder="E.g. Facebook, Google" autocomplete="off">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="lead_qa_source_active"
                                    name="status" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="lead_qa_source_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveLeadSource">
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
    const leadParentModalId = 'leadModal';
    let leadPausedParentModalId = null;
    let leadResumeScheduled = false;

    ['leadAddStatusModal', 'leadAddSourceModal'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
    });

    if (!window.__leadQaAlertGuardInstalled) {
        window.__leadQaAlertGuardInstalled = true;
        window.__leadQaSilent = false;

        const nativeAlert = window.alert;
        window.alert = function () {
            if (window.__leadQaSilent) return;
            return nativeAlert.apply(window, arguments);
        };

        if (typeof window.showAppToast === 'function') {
            const nativeToast = window.showAppToast;
            window.showAppToast = function () {
                if (window.__leadQaSilent) return;
                return nativeToast.apply(window, arguments);
            };
        }

        if (typeof window.showFloatingAlert === 'function') {
            const nativeFloating = window.showFloatingAlert;
            window.showFloatingAlert = function () {
                if (window.__leadQaSilent) return;
                return nativeFloating.apply(window, arguments);
            };
        }
    }

    function leadQaMutePopups(on) {
        window.__leadQaSilent = !!on;
        if (typeof toastr === 'undefined') return;
        if (on) {
            if (!window.__leadQaToastrBackup) {
                window.__leadQaToastrBackup = {
                    success: toastr.success,
                    error: toastr.error,
                    info: toastr.info,
                    warning: toastr.warning
                };
            }
            toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
        } else if (window.__leadQaToastrBackup) {
            toastr.success = window.__leadQaToastrBackup.success;
            toastr.error = window.__leadQaToastrBackup.error;
            toastr.info = window.__leadQaToastrBackup.info;
            toastr.warning = window.__leadQaToastrBackup.warning;
        }
    }

    function leadQaGetBsModal(el) {
        if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
        return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
    }

    function leadQaHideModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const bs = leadQaGetBsModal(el);
        if (bs) bs.hide();
        else $('#' + id).modal('hide');
    }

    function leadQaShowModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const bs = leadQaGetBsModal(el);
        if (bs) bs.show();
        else $('#' + id).modal('show');
    }

    function leadQaClearStrayBackdrops() {
        document.querySelectorAll('.modal-backdrop').forEach(function (b) {
            b.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
    }

    function leadQaResumeParentModal() {
        if (!leadPausedParentModalId || leadResumeScheduled) return;
        const parentId = leadPausedParentModalId;
        leadPausedParentModalId = null;
        leadResumeScheduled = true;
        setTimeout(function () {
            leadResumeScheduled = false;
            leadQaClearStrayBackdrops();
            leadQaShowModal(parentId);
        }, 200);
    }

    function leadQaOpenOverParent(quickModalId) {
        const parentEl = document.getElementById(leadParentModalId);
        const openQuick = function () {
            leadQaShowModal(quickModalId);
        };
        const parentVisible = parentEl && (
            parentEl.classList.contains('show') ||
            $(parentEl).is(':visible')
        );

        if (parentVisible) {
            leadPausedParentModalId = leadParentModalId;
            leadResumeScheduled = false;
            const onParentHidden = function () {
                parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                openQuick();
            };
            parentEl.addEventListener('hidden.bs.modal', onParentHidden);
            leadQaHideModal(leadParentModalId);
        } else {
            leadPausedParentModalId = null;
            openQuick();
        }
    }

    ['leadAddStatusModal', 'leadAddSourceModal'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el || el.dataset.leadQaParentResumeBound) return;
        el.dataset.leadQaParentResumeBound = '1';
        el.addEventListener('hidden.bs.modal', function () {
            leadQaResumeParentModal();
        });
    });

    function leadQaEscapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function leadQaShowFormError(errorId, message, formEl) {
        const box = document.getElementById(errorId);
        if (!box) return;
        let messages = [];
        if (Array.isArray(message)) {
            messages = message.filter(Boolean);
        } else if (message) {
            messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
        }
        if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

        let html = '<div class="lead-qa-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
        if (messages.length === 1) {
            html += '<div>' + leadQaEscapeHtml(messages[0]) + '</div>';
        } else {
            html += '<ul>';
            messages.forEach(function (m) { html += '<li>' + leadQaEscapeHtml(m) + '</li>'; });
            html += '</ul>';
        }
        box.innerHTML = html;
        box.classList.add('is-visible');
        if (formEl) {
            try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
        }
    }

    function leadQaClearFormError(errorId, formEl) {
        const box = document.getElementById(errorId);
        if (box) {
            box.classList.remove('is-visible');
            box.innerHTML = '';
        }
        if (formEl) {
            $(formEl).find('.is-invalid').removeClass('is-invalid');
        }
    }

    function leadQaMarkFieldErrors(formEl, errors) {
        if (!formEl || !errors) return;
        Object.keys(errors).forEach(function (field) {
            const input = formEl.querySelector('[name="' + field + '"]');
            if (input) input.classList.add('is-invalid');
        });
    }

    function leadQaExtractErrorPayload(xhr) {
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

    function leadQaHandleSaveFail(errorId, formEl, xhr) {
        const payload = leadQaExtractErrorPayload(xhr);
        leadQaClearFormError(errorId, formEl);
        if (payload.errors) leadQaMarkFieldErrors(formEl, payload.errors);
        leadQaShowFormError(errorId, payload.messages, formEl);
    }

    function leadQaAppendOption(selectId, id, text, selectIt) {
        const el = document.getElementById(selectId);
        if (!el || id == null) return;
        const value = String(id);
        const label = text || ('#' + value);
        let opt = el.querySelector('option[value="' + value.replace(/"/g, '\\"') + '"]');
        if (!opt) {
            opt = new Option(label, value, false, false);
            el.appendChild(opt);
        } else {
            opt.text = label;
        }
        if (selectIt) {
            el.value = value;
            $(el).trigger('change');
        }
    }

    function leadQaPostJson(url, data) {
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

    window.openLeadStatusModal = function () {
        const form = document.getElementById('leadAddStatusForm');
        if (!form) return;
        form.reset();
        leadQaClearFormError('leadStatusFormError', form);
        const active = document.getElementById('lead_qa_status_active');
        if (active) active.checked = true;
        const progress = document.getElementById('lead_qa_status_progress');
        if (progress) progress.value = '0';
        leadQaOpenOverParent('leadAddStatusModal');
        setTimeout(function () {
            const nameEl = document.getElementById('lead_qa_status_name');
            if (nameEl) nameEl.focus();
        }, 350);
    };

    window.openLeadSourceModal = function () {
        const form = document.getElementById('leadAddSourceForm');
        if (!form) return;
        form.reset();
        leadQaClearFormError('leadSourceFormError', form);
        const active = document.getElementById('lead_qa_source_active');
        if (active) active.checked = true;
        leadQaOpenOverParent('leadAddSourceModal');
        setTimeout(function () {
            const nameEl = document.getElementById('lead_qa_source_name');
            if (nameEl) nameEl.focus();
        }, 350);
    };

    window.saveLeadStatus = function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#leadAddStatusForm');
        const btn = $('#btnSaveLeadStatus');
        if (!form.length) return false;

        leadQaClearFormError('leadStatusFormError', form[0]);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        leadQaMutePopups(true);

        leadQaPostJson("{{ route('lead_status.store') }}", form.serialize())
            .done(function (response) {
                if (response && response.success && response.status) {
                    const s = response.status;
                    if (!s.status) {
                        leadQaShowFormError('leadStatusFormError',
                            @json(__('Status saved, but it is inactive so it was not selected.')), form[0]);
                        return;
                    }
                    leadQaAppendOption('status_id', s.id, s.name, true);
                    leadQaHideModal('leadAddStatusModal');
                    form[0].reset();
                    const active = document.getElementById('lead_qa_status_active');
                    if (active) active.checked = true;
                    return;
                }
                leadQaShowFormError('leadStatusFormError',
                    (response && response.message) || @json(__('Could not save status.')), form[0]);
            })
            .fail(function (xhr) {
                leadQaHandleSaveFail('leadStatusFormError', form[0], xhr);
            })
            .always(function () {
                leadQaMutePopups(false);
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
            });

        return false;
    };

    window.saveLeadSource = function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#leadAddSourceForm');
        const btn = $('#btnSaveLeadSource');
        if (!form.length) return false;

        leadQaClearFormError('leadSourceFormError', form[0]);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        leadQaMutePopups(true);

        leadQaPostJson("{{ route('lead_source.store') }}", form.serialize())
            .done(function (response) {
                if (response && response.success && response.source) {
                    const s = response.source;
                    if (!s.status) {
                        leadQaShowFormError('leadSourceFormError',
                            @json(__('Source saved, but it is inactive so it was not selected.')), form[0]);
                        return;
                    }
                    leadQaAppendOption('source_id', s.id, s.name, true);
                    leadQaHideModal('leadAddSourceModal');
                    form[0].reset();
                    const active = document.getElementById('lead_qa_source_active');
                    if (active) active.checked = true;
                    return;
                }
                leadQaShowFormError('leadSourceFormError',
                    (response && response.message) || @json(__('Could not save source.')), form[0]);
            })
            .fail(function (xhr) {
                leadQaHandleSaveFail('leadSourceFormError', form[0], xhr);
            })
            .always(function () {
                leadQaMutePopups(false);
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
            });

        return false;
    };
})();
</script>
