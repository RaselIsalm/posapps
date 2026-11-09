<style>
    .exp-field-group .btn-exp-add {
        min-width: 42px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .exp-field-group:has(.btn-exp-add) .form-select {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .exp-form-error {
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

    .exp-form-error.is-visible {
        display: block;
    }

    .exp-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .exp-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .exp-quick-modal {
        z-index: 1065 !important;
    }

    .modal-backdrop.exp-quick-backdrop {
        z-index: 1060 !important;
    }

    @media (max-width: 575.98px) {
        .exp-field-group .btn-exp-add {
            min-width: 38px;
        }
    }
</style>

{{-- Quick Add Category --}}
@can('create_expenses_categories')
    <div class="modal fade exp-quick-modal" id="expAddCategoryModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-tags me-2"></i>Add Expense Category</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="expAddCategoryForm" onsubmit="return saveExpCategory(event)">
                        @csrf
                        <div class="exp-form-error" id="expCategoryFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Category Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Category name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Category Code *</label>
                            <div class="input-group">
                                <input type="text" name="code" id="exp_category_code" class="form-control" required
                                    placeholder="EXP-10000" readonly>
                                <button type="button" class="btn btn-outline-primary" id="expGenerateCodeBtn">Generate</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="exp_category_is_active"
                                    name="is_active" checked style="width: 40px; height: 20px;">
                                <label class="form-check-label ms-2" for="exp_category_is_active">Active</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveExpCategory">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- Quick Add Branch --}}
@can('create_branches')
    <div class="modal fade exp-quick-modal" id="expAddBranchModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="expAddBranchForm" onsubmit="return saveExpBranch(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="exp-form-error" id="expBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="expBranchManagerSelect" class="form-control" required>
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
                                    <select name="account_id" id="expBranchAccountSelect" class="form-control" required>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="exp_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="exp_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveExpBranch">
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
        'managerSelectId' => 'expBranchManagerSelect',
        'accountSelectId' => 'expBranchAccountSelect',
        'departmentSelectId' => 'expUnusedDepartmentSelect',
        'roleSelectId' => 'expUnusedRoleSelect',
        'branchParentModalId' => 'expAddBranchModal',
        'employeeParentModalId' => 'expAddBranchModal',
        'roles' => $employeeRoles ?? $roles ?? [],
    ],
])

{{-- Quick Add Account --}}
@can('create_accounts')
    <div class="modal fade exp-quick-modal" id="expAddAccountModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Account</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="expAddAccountForm" onsubmit="return saveExpAccount(event)">
                        @csrf
                        <div class="exp-form-error" id="expAccountFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Account name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Type *</label>
                                <select name="type" id="exp_account_type" class="form-control" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile banking">Mobile Banking</option>
                                    <option value="digital wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">
                                    Account Number <span id="exp_account_number_required" class="text-danger d-none">*</span>
                                </label>
                                <input type="text" name="account_number" id="exp_account_number" class="form-control"
                                    placeholder="Not required for cash">
                                <small id="exp_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="exp_account_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="exp_account_is_active">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="exp_account_is_default"
                                        name="is_default" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="exp_account_is_default">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="exp_account_manual"
                                        name="allow_manual_amount" style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="exp_account_manual">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveExpAccount">
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
        // Move nested quick-add modals to body so they stack above Expense modal
        ['expAddCategoryModal', 'expAddBranchModal', 'expAddAccountModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        $(document).off('show.bs.modal.expQuick', '.exp-quick-modal')
            .on('show.bs.modal.expQuick', '.exp-quick-modal', function () {
                setTimeout(function () {
                    $('.modal-backdrop').not('.exp-quick-backdrop').last().addClass('exp-quick-backdrop');
                }, 10);
            });

        function expEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function expShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="exp-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + expEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + expEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function expClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.exp-field-error').remove();
            }
        }

        function expMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block exp-field-error';
                feedback.textContent = msg;
                input.insertAdjacentElement('afterend', feedback);
            });
        }

        function expExtractErrorPayload(xhr) {
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

        function expHandleSaveFail(errorId, formEl, xhr) {
            const payload = expExtractErrorPayload(xhr);
            expClearFormError(errorId, formEl);
            if (payload.errors) expMarkFieldErrors(formEl, payload.errors);
            expShowFormError(errorId, payload.messages, formEl);
        }

        function expAppendOption(selectId, id, text, selectIt) {
            const el = document.getElementById(selectId);
            if (!el || id == null) return;
            const value = String(id);
            if ($(el).find('option[value="' + value + '"]').length === 0) {
                el.appendChild(new Option(text || ('#' + value), value, false, false));
            }
            if (selectIt) {
                el.value = value;
                $(el).trigger('change');
            }
        }

        function expPostJson(url, data, isFormData) {
            const options = {
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };
            if (isFormData) {
                options.processData = false;
                options.contentType = false;
            }
            return $.ajax(options);
        }

        function expGenerateCategoryCode() {
            return 'EXP-' + Math.floor(10000 + Math.random() * 90000);
        }

        window.openExpCategoryModal = function () {
            const form = document.getElementById('expAddCategoryForm');
            if (!form) return;
            form.reset();
            expClearFormError('expCategoryFormError', form);
            const code = document.getElementById('exp_category_code');
            if (code) code.value = expGenerateCategoryCode();
            const active = document.getElementById('exp_category_is_active');
            if (active) active.checked = true;
            $('#expAddCategoryModal').modal('show');
        };

        window.openExpBranchModal = function () {
            const form = document.getElementById('expAddBranchForm');
            if (!form) return;
            form.reset();
            expClearFormError('expBranchFormError', form);
            const active = document.getElementById('exp_branch_is_active');
            if (active) active.checked = true;
            $('#expAddBranchModal').modal('show');
        };

        window.openExpAccountModal = function () {
            const form = document.getElementById('expAddAccountForm');
            if (!form) return;
            form.reset();
            expClearFormError('expAccountFormError', form);
            const active = document.getElementById('exp_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('exp_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncExpAccountNumberRequirement();
            $('#expAddAccountModal').modal('show');
        };

        window.saveExpCategory = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#expAddCategoryForm');
            const btn = $('#btnSaveExpCategory');
            if (!form.length) return false;

            expClearFormError('expCategoryFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            expPostJson("{{ route('expenses-categories.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.category) {
                        const c = response.category;
                        if (!c.status) {
                            expShowFormError('expCategoryFormError', @json(__('Category saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        expAppendOption('category_id', c.id, c.name, true);
                        $('#expAddCategoryModal').modal('hide');
                        form[0].reset();
                        return;
                    }
                    expShowFormError('expCategoryFormError', (response && response.message) || @json(__('Could not save category.')), form[0]);
                })
                .fail(function (xhr) {
                    expHandleSaveFail('expCategoryFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveExpBranch = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#expAddBranchForm');
            const btn = $('#btnSaveExpBranch');
            if (!form.length) return false;

            expClearFormError('expBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            expPostJson("{{ route('branches.store') }}", new FormData(form[0]), true)
                .done(function (response) {
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            expShowFormError('expBranchFormError', @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        expAppendOption('branch_id', b.id, b.name, true);
                        $('#expAddBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('exp_branch_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    expShowFormError('expBranchFormError', (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function (xhr) {
                    expHandleSaveFail('expBranchFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        function syncExpAccountNumberRequirement() {
            const typeEl = document.getElementById('exp_account_type');
            const numberEl = document.getElementById('exp_account_number');
            const requiredMark = document.getElementById('exp_account_number_required');
            const hintEl = document.getElementById('exp_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        window.saveExpAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#expAddAccountForm');
            const btn = $('#btnSaveExpAccount');
            if (!form.length) return false;

            expClearFormError('expAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            expPostJson("{{ route('accounts.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            expShowFormError('expAccountFormError', @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        expAppendOption('account_id', a.id, a.name, true);
                        expAppendOption('expBranchAccountSelect', a.id, a.name, false);
                        $('#expAddAccountModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('exp_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    expShowFormError('expAccountFormError', (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    expHandleSaveFail('expAccountFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('click.expGenCode', '#expGenerateCodeBtn')
            .on('click.expGenCode', '#expGenerateCodeBtn', function () {
                const code = document.getElementById('exp_category_code');
                if (code) code.value = expGenerateCategoryCode();
            });

        $(document).off('change.expAccType', '#exp_account_type')
            .on('change.expAccType', '#exp_account_type', syncExpAccountNumberRequirement);
    })();
</script>
