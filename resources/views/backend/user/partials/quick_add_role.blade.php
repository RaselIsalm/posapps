{{-- User Create: Role silent quick-add (full fields) --}}
@php
    $roleSelectId = $roleSelectId ?? 'role';
@endphp

<style>
    .usr-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .usr-field-group > .form-control,
    .usr-field-group > .form-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .usr-field-group > .btn-usr-add {
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .usr-form-error {
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

    .usr-form-error.is-visible {
        display: block;
    }

    .usr-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .usr-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    #usrAddRoleModal .modal-dialog {
        width: calc(100% - 1.5rem);
        max-width: 520px;
        margin: 0.75rem auto;
    }

    @media (max-width: 575.98px) {
        .usr-field-group > .btn-usr-add {
            min-width: 38px;
        }

        #usrAddRoleModal .modal-dialog {
            max-width: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #usrAddRoleModal .modal-body {
            max-height: min(75dvh, 480px);
            overflow-y: auto;
        }
    }
</style>

@can('manage_roles')
    <div class="modal fade" id="usrAddRoleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Add Role</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="usrAddRoleForm" onsubmit="return saveUsrRole(event)" novalidate>
                        @csrf
                        <div class="usr-form-error" id="usrRoleFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1" for="usr_role_name">Role Name *</label>
                            <input type="text" name="name" id="usr_role_name" class="form-control" required
                                placeholder="Role name" maxlength="255" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1" for="usr_role_description">Description</label>
                            <textarea name="description" id="usr_role_description" class="form-control" rows="2"
                                placeholder="Optional description" maxlength="1000"></textarea>
                        </div>
                        <p class="small text-muted mb-3">
                            Permissions can be assigned later from Roles &amp; Permissions.
                        </p>
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveUsrRole">
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
        if (window.__usrRoleQuickAddInstalled) return;
        window.__usrRoleQuickAddInstalled = true;

        const roleSelectId = @json($roleSelectId);

        const modalEl = document.getElementById('usrAddRoleModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (!window.__usrAlertGuardInstalled) {
            window.__usrAlertGuardInstalled = true;
            window.__usrQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__usrQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__usrQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
            if (typeof window.showFloatingAlert === 'function') {
                const nativeFloat = window.showFloatingAlert;
                window.showFloatingAlert = function () {
                    if (window.__usrQuickAddSilent) return;
                    return nativeFloat.apply(window, arguments);
                };
            }
        }

        function usrMutePopups(on) {
            window.__usrQuickAddSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__usrToastrBackup) {
                    window.__usrToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__usrToastrBackup) {
                toastr.success = window.__usrToastrBackup.success;
                toastr.error = window.__usrToastrBackup.error;
                toastr.info = window.__usrToastrBackup.info;
                toastr.warning = window.__usrToastrBackup.warning;
            }
        }

        function usrGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function usrHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = usrGetBsModal(el);
            if (bs) bs.hide();
            else $('#' + id).modal('hide');
        }

        function usrShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = usrGetBsModal(el);
            if (bs) bs.show();
            else $('#' + id).modal('show');
        }

        function usrEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function usrShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="usr-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + usrEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + usrEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function usrClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function usrMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function usrExtractErrorPayload(xhr) {
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

        function usrHandleSaveFail(errorId, formEl, xhr) {
            const payload = usrExtractErrorPayload(xhr);
            usrClearFormError(errorId, formEl);
            if (payload.errors) usrMarkFieldErrors(formEl, payload.errors);
            usrShowFormError(errorId, payload.messages, formEl);
        }

        function usrAppendOption(selectId, value, text, selectIt) {
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

        function usrPostJson(url, data) {
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

        window.openUsrRoleModal = function () {
            const form = document.getElementById('usrAddRoleForm');
            if (!form) return;
            form.reset();
            usrClearFormError('usrRoleFormError', form);
            usrShowModal('usrAddRoleModal');
            setTimeout(function () {
                const nameEl = document.getElementById('usr_role_name');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveUsrRole = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#usrAddRoleForm');
            const btn = $('#btnSaveUsrRole');
            if (!form.length) return false;

            usrClearFormError('usrRoleFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            usrMutePopups(true);

            usrPostJson("{{ route('roles.store') }}", form.serialize())
                .done(function (response) {
                    if (response && response.success && response.role) {
                        const r = response.role;
                        // User form uses role name as option value
                        usrAppendOption(roleSelectId, r.name, r.name, true);
                        usrHideModal('usrAddRoleModal');
                        form[0].reset();
                        return;
                    }
                    usrShowFormError('usrRoleFormError',
                        (response && response.message) || @json(__('Could not save role.')), form[0]);
                })
                .fail(function (xhr) {
                    usrHandleSaveFail('usrRoleFormError', form[0], xhr);
                })
                .always(function () {
                    usrMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
@endpush
