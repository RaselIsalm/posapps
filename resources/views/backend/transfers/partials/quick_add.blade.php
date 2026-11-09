@push('css')
    <style>
        .tr-field-group .btn-tr-add {
            min-width: 42px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .tr-field-group:has(.btn-tr-add) .form-select,
        .tr-field-group:has(.btn-tr-add) .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .tr-form-error {
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

        .tr-form-error.is-visible {
            display: block;
        }

        .tr-form-error-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .tr-form-error ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        @media (max-width: 575.98px) {
            .tr-field-group .btn-tr-add {
                min-width: 38px;
            }
        }
    </style>
@endpush

@can('create_branches')
    <div class="modal fade" id="trAddBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="trAddBranchForm" onsubmit="return saveTrBranch(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="tr-form-error" id="trBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="trBranchManagerSelect" class="form-control" required>
                                        <option value="">Select Manager</option>
                                        @foreach ($branchManagers ?? [] as $manager)
                                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_users')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestManagerModal()" title="Add Manager">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Account *</label>
                                <div class="input-group nest-field-group">
                                    <select name="account_id" id="trBranchAccountSelect" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($accounts ?? [] as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestAccountModal()" title="Add Account">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Address *</label>
                                <input type="text" name="address" class="form-control" required placeholder="Branch address">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone (Mobile)</label>
                                <input type="text" name="phone" class="form-control" placeholder="Mobile">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Telephone (Landline)</label>
                                <input type="text" name="telephone" class="form-control" placeholder="Landline">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">BIN No</label>
                                <input type="text" name="bin_number" class="form-control" placeholder="BIN number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Website</label>
                                <input type="text" name="website" class="form-control" placeholder="https://example.com">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Branch Logo</label>
                                <input type="file" name="branch_logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="tr_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="tr_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveTrBranch">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@include('backend.shared.partials.nested_branch_employee_quick_add', [
    'nx' => [
        'managerSelectId' => 'trBranchManagerSelect',
        'accountSelectId' => 'trBranchAccountSelect',
        'departmentSelectId' => 'trUnusedDepartmentSelect',
        'roleSelectId' => 'trUnusedRoleSelect',
        'branchParentModalId' => 'trAddBranchModal',
        'employeeParentModalId' => 'trAddBranchModal',
        'roles' => $employeeRoles ?? $roles ?? [],
    ],
])

@push('js')
    <script>
        (function () {
            let trBranchTargetSelect = 'from_branch_id';

            function trEscapeHtml(text) {
                return String(text == null ? '' : text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function trShowFormError(errorId, message, formEl) {
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
                    '<div class="tr-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
                if (messages.length === 1) {
                    html += '<div>' + trEscapeHtml(messages[0]) + '</div>';
                } else {
                    html += '<ul>';
                    messages.forEach(function (m) {
                        html += '<li>' + trEscapeHtml(m) + '</li>';
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

            function trClearFormError(errorId, formEl) {
                const box = document.getElementById(errorId);
                if (box) {
                    box.classList.remove('is-visible');
                    box.innerHTML = '';
                }
                if (formEl) {
                    $(formEl).find('.is-invalid').removeClass('is-invalid');
                    $(formEl).find('.tr-field-error').remove();
                }
            }

            function trMarkFieldErrors(formEl, errors) {
                if (!formEl || !errors) return;
                Object.keys(errors).forEach(function (field) {
                    const input = formEl.querySelector('[name="' + field + '"]');
                    if (!input) return;
                    input.classList.add('is-invalid');
                    const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    if (!msg) return;
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block tr-field-error';
                    feedback.textContent = msg;
                    input.insertAdjacentElement('afterend', feedback);
                });
            }

            function trExtractErrorPayload(xhr) {
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
                if (xhr.status === 0) {
                    return {
                        messages: [@json(__('Network error. Please check your connection.'))],
                        errors: null
                    };
                }
                return {
                    messages: [@json(__('Something went wrong. Please try again.'))],
                    errors: null
                };
            }

            function trHandleSaveFail(errorId, formEl, xhr) {
                const payload = trExtractErrorPayload(xhr);
                trClearFormError(errorId, formEl);
                if (payload.errors) trMarkFieldErrors(formEl, payload.errors);
                trShowFormError(errorId, payload.messages, formEl);
            }

            function trAppendOption(selectId, id, text, selectIt) {
                const el = document.getElementById(selectId);
                if (!el || id == null) return;
                const value = String(id);
                let exists = false;
                for (let i = 0; i < el.options.length; i++) {
                    if (el.options[i].value === value) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) {
                    el.appendChild(new Option(text || ('#' + value), value, false, false));
                }
                if (selectIt) {
                    el.value = value;
                    $(el).trigger('change');
                }
            }

            window.openTrBranchModal = function (targetSelectId) {
                const form = document.getElementById('trAddBranchForm');
                if (!form) return;
                trBranchTargetSelect = targetSelectId || 'from_branch_id';
                form.reset();
                trClearFormError('trBranchFormError', form);
                const active = document.getElementById('tr_branch_is_active');
                if (active) active.checked = true;
                $('#trAddBranchModal').modal('show');
            };

            window.saveTrBranch = function (e) {
                e.preventDefault();
                e.stopPropagation();
                const form = $('#trAddBranchForm');
                const btn = $('#btnSaveTrBranch');
                if (!form.length) return false;

                trClearFormError('trBranchFormError', form[0]);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: "{{ route('branches.store') }}",
                    type: 'POST',
                    data: new FormData(form[0]),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            trShowFormError('trBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        // Keep From/To lists in sync; select only the field that opened the modal
                        trAppendOption('from_branch_id', b.id, b.name, trBranchTargetSelect === 'from_branch_id');
                        trAppendOption('to_branch_id', b.id, b.name, trBranchTargetSelect === 'to_branch_id');
                        $('#trAddBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('tr_branch_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    trShowFormError('trBranchFormError', (response && response.message) || @json(__('Could not save branch.')),
                        form[0]);
                }).fail(function (xhr) {
                    trHandleSaveFail('trBranchFormError', form[0], xhr);
                }).always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

                return false;
            };
        })();
    </script>
@endpush
