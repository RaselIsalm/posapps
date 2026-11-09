{{-- Sale Edit: Customer + Branch quick-add (silent AJAX, no toaster / reload) --}}

<style>
    .sale-field-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100%;
    }

    .sale-field-group .select2-container {
        flex: 1 1 auto;
        width: 1% !important;
    }

    .sale-field-group .select2-container .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }

    .sale-field-group:has(.btn-sale-add) .select2-container .select2-selection--single,
    .sale-field-group:has(.btn-sale-add) > .form-select {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .sale-field-group .btn-sale-add {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 42px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sale-form-error {
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

    .sale-form-error.is-visible {
        display: block;
    }

    .sale-form-error-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .sale-form-error ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    @media (max-width: 575.98px) {
        .sale-field-group .btn-sale-add {
            min-width: 38px;
        }
    }
</style>

@can('create_customers')
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addCustomerForm" onsubmit="return saveSaleCustomer(event)">
                        @csrf
                        <div class="sale-form-error" id="saleCustomerFormError" role="alert"></div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Customer Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Phone *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold text-muted mb-1">Email (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Address (Optional)</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">Note (Optional)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSaleCustomer">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@can('create_branches')
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addBranchForm" onsubmit="return saveSaleBranch(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="sale-form-error" id="saleBranchFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Branch name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Manager *</label>
                                <div class="input-group nest-field-group">
                                    <select name="manager_id" id="saleBranchManagerSelect" class="form-control" required>
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
                                    <select name="account_id" id="saleBranchAccountSelect" class="form-control" required>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="sale_branch_is_active"
                                        name="is_active" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="sale_branch_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSaleBranch">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

@can('create_employees')
    <div class="modal fade" id="addSalesmanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Salesman</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="addSalesmanForm" onsubmit="return saveSaleSalesman(event)" novalidate>
                        @csrf
                        <div class="sale-form-error" id="saleSalesmanFormError" role="alert"></div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Employee name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Email *</label>
                                <input type="email" name="email" class="form-control" required placeholder="Login email">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone *</label>
                                <input type="tel" name="phone" class="form-control" required placeholder="Phone number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Branch *</label>
                                <select name="branch_id" id="saleSalesmanBranch" class="form-control" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Department *</label>
                                <div class="input-group nest-field-group">
                                    <select name="department_id" id="saleSalesmanDepartmentSelect" class="form-control" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments ?? [] as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_departments')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestDepartmentModal()" title="Add Department">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Role *</label>
                                <div class="input-group nest-field-group">
                                    <select name="role" id="saleSalesmanRoleSelect" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach ($employeeRoles ?? [] as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('manage_roles')
                                        <button type="button" class="btn btn-outline-primary btn-nest-add"
                                            onclick="openNxNestRoleModal()" title="Add Role">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Password *</label>
                                <input type="password" name="password" class="form-control" required minlength="6"
                                    autocomplete="new-password" placeholder="Login password">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Confirm Password *</label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    minlength="6" autocomplete="new-password" placeholder="Confirm password">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Salary *</label>
                                <input type="number" name="salary" class="form-control" required step="any"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Max Commission</label>
                                <input type="number" name="max_commission" class="form-control" step="any"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Sale Target</label>
                                <input type="number" name="sale_target" class="form-control" step="any"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Joining Date *</label>
                                <input type="date" name="joining_date" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1">Leaving Date</label>
                                <input type="date" name="leaving_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="sale_salesman_status"
                                        name="status" checked style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="sale_salesman_status">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSaleSalesman">
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
        'managerSelectId' => 'saleBranchManagerSelect',
        'accountSelectId' => 'saleBranchAccountSelect',
        'departmentSelectId' => 'saleSalesmanDepartmentSelect',
        'roleSelectId' => 'saleSalesmanRoleSelect',
        'branchParentModalId' => 'addBranchModal',
        'employeeParentModalId' => 'addSalesmanModal',
        'roles' => $employeeRoles ?? [],
    ],
])

<script>
    (function () {
        if (window.__saleEditQuickAddInstalled) return;
        window.__saleEditQuickAddInstalled = true;

        ['addCustomerModal', 'addBranchModal', 'addSalesmanModal'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        if (!window.__saleAlertGuardInstalled) {
            window.__saleAlertGuardInstalled = true;
            window.__saleQuickAddSilent = false;

            const nativeAlert = window.alert;
            window.alert = function () {
                if (window.__saleQuickAddSilent) return;
                return nativeAlert.apply(window, arguments);
            };

            if (typeof window.showAppToast === 'function') {
                const nativeToast = window.showAppToast;
                window.showAppToast = function () {
                    if (window.__saleQuickAddSilent) return;
                    return nativeToast.apply(window, arguments);
                };
            }
        }

        function saleMutePopups(on) {
            window.__saleQuickAddSilent = !!on;

            // Wrap late-defined page toast helpers on first mute
            if (typeof window.showCustomToast === 'function' && !window.__saleCustomToastWrapped) {
                window.__saleCustomToastWrapped = true;
                const nativeCustom = window.showCustomToast;
                window.showCustomToast = function () {
                    if (window.__saleQuickAddSilent) return;
                    return nativeCustom.apply(window, arguments);
                };
            }

            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__saleToastrBackup) {
                    window.__saleToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__saleToastrBackup) {
                toastr.success = window.__saleToastrBackup.success;
                toastr.error = window.__saleToastrBackup.error;
                toastr.info = window.__saleToastrBackup.info;
                toastr.warning = window.__saleToastrBackup.warning;
            }
        }

        function saleEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function saleShowFormError(errorId, message, formEl) {
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
                '<div class="sale-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + saleEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + saleEscapeHtml(m) + '</li>';
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

        function saleClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function saleMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function saleExtractErrorPayload(xhr) {
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

        function saleHandleSaveFail(errorId, formEl, xhr) {
            const payload = saleExtractErrorPayload(xhr);
            saleClearFormError(errorId, formEl);
            if (payload.errors) saleMarkFieldErrors(formEl, payload.errors);
            saleShowFormError(errorId, payload.messages, formEl);
        }

        function saleAppendSelectOption(selectId, id, text, selectIt) {
            const $el = $('#' + selectId);
            if (!$el.length || id == null) return;
            const value = String(id);
            if ($el.find('option[value="' + value + '"]').length === 0) {
                $el.append(new Option(text || ('#' + value), value, false, false));
            }
            if (selectIt) {
                $el.val(value).trigger('change');
            }
        }

        function salePostJson(url, data, isFormData) {
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

        window.openSaleCustomerModal = function () {
            const form = document.getElementById('addCustomerForm');
            if (!form) return;
            form.reset();
            saleClearFormError('saleCustomerFormError', form);
            $('#addCustomerModal').modal('show');
        };

        window.openSaleBranchModal = function () {
            const form = document.getElementById('addBranchForm');
            if (!form) return;
            form.reset();
            saleClearFormError('saleBranchFormError', form);
            const active = document.getElementById('sale_branch_is_active');
            if (active) active.checked = true;
            $('#addBranchModal').modal('show');
        };

        window.saveSaleCustomer = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addCustomerForm');
            const btn = $('#btnSaveSaleCustomer');
            if (!form.length) return false;

            saleClearFormError('saleCustomerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            saleMutePopups(true);

            salePostJson("{{ route('customers.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.customer) {
                        const c = response.customer;
                        if (c.status === false) {
                            saleShowFormError('saleCustomerFormError',
                                @json(__('Customer saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            return;
                        }
                        saleAppendSelectOption('customer_id', c.id, c.name, true);
                        $('#addCustomerModal').modal('hide');
                        form[0].reset();
                        saleClearFormError('saleCustomerFormError', form[0]);
                        return;
                    }
                    saleShowFormError('saleCustomerFormError',
                        (response && response.message) || @json(__('Could not save customer.')), form[0]);
                })
                .fail(function (xhr) {
                    saleHandleSaveFail('saleCustomerFormError', form[0], xhr);
                })
                .always(function () {
                    saleMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.saveSaleBranch = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addBranchForm');
            const btn = $('#btnSaveSaleBranch');
            if (!form.length) return false;

            saleClearFormError('saleBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            saleMutePopups(true);

            salePostJson("{{ route('branches.store') }}", new FormData(form[0]), true)
                .done(function (response) {
                    if (response && response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            saleShowFormError('saleBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('sale_branch_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        saleAppendSelectOption('branch_id', b.id, b.name, true);
                        saleAppendSelectOption('saleSalesmanBranch', b.id, b.name, false);
                        $('#addBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('sale_branch_is_active');
                        if (active) active.checked = true;
                        saleClearFormError('saleBranchFormError', form[0]);
                        return;
                    }
                    saleShowFormError('saleBranchFormError',
                        (response && response.message) || @json(__('Could not save branch.')), form[0]);
                })
                .fail(function (xhr) {
                    saleHandleSaveFail('saleBranchFormError', form[0], xhr);
                })
                .always(function () {
                    saleMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        window.openSaleSalesmanModal = function () {
            const form = document.getElementById('addSalesmanForm');
            if (!form) return;
            form.reset();
            saleClearFormError('saleSalesmanFormError', form);
            const status = document.getElementById('sale_salesman_status');
            if (status) status.checked = true;
            const join = form.querySelector('[name="joining_date"]');
            if (join) join.value = @json(date('Y-m-d'));
            const branchSel = document.getElementById('saleSalesmanBranch');
            const pageBranch = document.getElementById('branch_id');
            if (branchSel && pageBranch && pageBranch.value) {
                branchSel.value = pageBranch.value;
            }
            $('#addSalesmanModal').modal('show');
            setTimeout(function () {
                const nameEl = form.querySelector('[name="name"]');
                if (nameEl) nameEl.focus();
            }, 350);
        };

        window.saveSaleSalesman = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addSalesmanForm');
            const btn = $('#btnSaveSaleSalesman');
            if (!form.length) return false;

            saleClearFormError('saleSalesmanFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            saleMutePopups(true);

            const phone = String(form.find('[name="phone"]').val() || '').trim();
            const name = String(form.find('[name="name"]').val() || '').trim();

            salePostJson("{{ route('employees.store') }}", form.serialize(), false)
                .done(function (response) {
                    if (response && response.success && response.employee) {
                        const emp = response.employee;
                        if (emp.status === false) {
                            saleShowFormError('saleSalesmanFormError',
                                @json(__('Salesman saved, but inactive so not selected.')), form[0]);
                            return;
                        }
                        const label = (emp.name || name) + (phone ? ' (' + phone + ')' : '');
                        saleAppendSelectOption('seller_id', emp.id, label, true);
                        $('#addSalesmanModal').modal('hide');
                        form[0].reset();
                        const status = document.getElementById('sale_salesman_status');
                        if (status) status.checked = true;
                        saleClearFormError('saleSalesmanFormError', form[0]);
                        return;
                    }
                    saleShowFormError('saleSalesmanFormError',
                        (response && response.message) || @json(__('Could not save salesman.')), form[0]);
                })
                .fail(function (xhr) {
                    saleHandleSaveFail('saleSalesmanFormError', form[0], xhr);
                })
                .always(function () {
                    saleMutePopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };
    })();
</script>
