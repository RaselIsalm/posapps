{{-- Product Edit: Unit / Category / Brand quick-add (silent AJAX, teal header) --}}

@can('create_units')
<div class="modal fade" id="quickUnitModal" tabindex="-1" aria-labelledby="quickUnitModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold" id="quickUnitModalTitle"><i class="fas fa-ruler-combined me-2"></i>Add New Unit</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickUnitForm" onsubmit="return false;">
                <div class="modal-body pt-3">
                    <div class="prod-qa-form-error" id="prodUnitFormError" role="alert"></div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="quick_unit_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_unit_name" name="name" placeholder="Unit Name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_unit_short_code" class="form-label-sm">Short Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_unit_short_code" name="short_code" placeholder="Short Code" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_unit_base_unit" class="form-label-sm">Base Unit</label>
                            <select class="form-control" id="quick_unit_base_unit" name="base_unit">
                                <option value="">Select Base Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_unit_operator" class="form-label-sm">Operator</label>
                            <select class="form-control" id="quick_unit_operator" name="operator">
                                <option value="">Select Operator</option>
                                <option value="*">Multiply (*)</option>
                                <option value="/">Divide (/)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_unit_conversion_rate" class="form-label-sm">Conversion Rate <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" id="quick_unit_conversion_rate" name="conversion_rate" value="1" required>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label mb-0 fw-bold" for="quick_unit_active">Is Active</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_unit_active" checked name="is_active" style="width: 45px; height: 22px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickUnitSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickUnitSpinner" role="status"></span>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('create_categories')
<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-labelledby="quickCategoryModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold" id="quickCategoryModalTitle"><i class="fas fa-folder-plus me-2"></i>Add New Category</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickCategoryForm" enctype="multipart/form-data" onsubmit="return false;">
                <div class="modal-body pt-3">
                    <div class="prod-qa-form-error" id="prodCategoryFormError" role="alert"></div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="quick_category_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_category_name" name="name" placeholder="Category Name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_category_parent" class="form-label-sm">Parent Category</label>
                            <select class="form-control" id="quick_category_parent" name="parent_id">
                                <option value="">Select Parent</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_category_description" class="form-label-sm">Description</label>
                            <textarea style="caret-color: black;" class="form-control" id="quick_category_description" name="description" placeholder="Description" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label mb-0 fw-bold" for="quick_category_active">Is Active</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_category_active" checked name="is_active" style="width: 45px; height: 22px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_category_image" class="form-label-sm">Image</label>
                            <input type="file" class="form-control" id="quick_category_image" name="image" accept=".jpg,.jpeg,.png"
                                onchange="document.getElementById('quick_category_image_preview').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="col-12 mb-3 text-center">
                            <img id="quick_category_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                                class="img-fluid border rounded mt-2" alt="Image Preview" style="max-height: 80px; max-width: 80px;" />
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickCategorySaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickCategorySpinner" role="status"></span>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('create_brands')
<div class="modal fade" id="quickBrandModal" tabindex="-1" aria-labelledby="quickBrandModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-2">
                <h6 class="modal-title fw-bold" id="quickBrandModalTitle"><i class="fas fa-tags me-2"></i>Add New Brand</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickBrandForm" enctype="multipart/form-data" onsubmit="return false;">
                <div class="modal-body pt-3">
                    <div class="prod-qa-form-error" id="prodBrandFormError" role="alert"></div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="quick_brand_name" class="form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_brand_name" name="name" placeholder="Brand Name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_brand_description" class="form-label-sm">Description</label>
                            <textarea style="caret-color: black;" class="form-control" id="quick_brand_description" name="description" placeholder="Description" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label mb-0 fw-bold" for="quick_brand_active">Is Active</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_brand_active" checked name="is_active" style="width: 45px; height: 22px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="quick_brand_image" class="form-label-sm">Image</label>
                            <input type="file" class="form-control" id="quick_brand_image" name="image" accept=".jpg,.jpeg,.png"
                                onchange="document.getElementById('quick_brand_image_preview').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="col-12 mb-3 text-center">
                            <img id="quick_brand_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                                class="img-fluid border rounded mt-2" alt="Image Preview" style="max-height: 80px; max-width: 80px;" />
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickBrandSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickBrandSpinner" role="status"></span>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('css')
<style>
    .prod-qa-field-group {
        display: flex;
        align-items: stretch;
        gap: 0;
    }
    .prod-qa-field-group > .form-control,
    .prod-qa-field-group > .form-select {
        flex: 1 1 auto;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .prod-qa-field-group .btn-prod-qa-add {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 34px;
        width: 34px;
        height: auto;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 0;
        border-radius: 0 5px 5px 0;
    }
    .prod-qa-form-error {
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
    .prod-qa-form-error.is-visible { display: block; }
    .prod-qa-form-error-title { font-weight: 700; margin-bottom: 4px; }
    .prod-qa-form-error ul { margin: 0; padding-left: 1.1rem; }
    .prod-qa-field-error { font-size: 0.75rem; }
</style>
@endpush

@push('js')
<script>
(function () {
    if (window.__prodQaAlertGuardInstalled) return;
    window.__prodQaAlertGuardInstalled = true;
    window.__prodQaSilent = false;

    const nativeAlert = window.alert;
    window.alert = function () {
        if (window.__prodQaSilent) return;
        return nativeAlert.apply(window, arguments);
    };
    if (typeof window.showAppToast === 'function') {
        const nativeToast = window.showAppToast;
        window.showAppToast = function () {
            if (window.__prodQaSilent) return;
            return nativeToast.apply(window, arguments);
        };
    }
})();

function prodQaMute(on) {
    window.__prodQaSilent = !!on;
    if (typeof toastr === 'undefined') return;
    if (on) {
        if (!window.__prodQaToastrBackup) {
            window.__prodQaToastrBackup = {
                success: toastr.success,
                error: toastr.error,
                info: toastr.info,
                warning: toastr.warning
            };
        }
        toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
    } else if (window.__prodQaToastrBackup) {
        toastr.success = window.__prodQaToastrBackup.success;
        toastr.error = window.__prodQaToastrBackup.error;
        toastr.info = window.__prodQaToastrBackup.info;
        toastr.warning = window.__prodQaToastrBackup.warning;
    }
}

function prodQaEscape(text) {
    return String(text == null ? '' : text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function prodQaShowError(errorId, message, formEl) {
    const box = document.getElementById(errorId);
    if (!box) return;
    let messages = [];
    if (Array.isArray(message)) messages = message.filter(Boolean);
    else if (message && typeof message === 'object') {
        Object.keys(message).forEach(function (key) {
            const items = message[key];
            if (Array.isArray(items)) items.forEach(function (i) { messages.push(i); });
            else if (items) messages.push(items);
        });
    } else if (message) {
        messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
    }
    if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];
    let html = '<div class="prod-qa-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
    if (messages.length === 1) html += '<div>' + prodQaEscape(messages[0]) + '</div>';
    else {
        html += '<ul>';
        messages.forEach(function (m) { html += '<li>' + prodQaEscape(m) + '</li>'; });
        html += '</ul>';
    }
    box.innerHTML = html;
    box.classList.add('is-visible');
}

function prodQaClearError(errorId, formEl) {
    const box = document.getElementById(errorId);
    if (box) { box.classList.remove('is-visible'); box.innerHTML = ''; }
    if (formEl) {
        $(formEl).find('.is-invalid').removeClass('is-invalid');
        $(formEl).find('.prod-qa-field-error').remove();
    }
}

function prodQaMarkErrors(formEl, errors) {
    if (!formEl || !errors) return;
    Object.keys(errors).forEach(function (field) {
        const input = formEl.querySelector('[name="' + field + '"]');
        if (!input) return;
        input.classList.add('is-invalid');
        const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
        if (!msg) return;
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block prod-qa-field-error';
        feedback.textContent = msg;
        input.insertAdjacentElement('afterend', feedback);
    });
}

function prodQaExtract(xhr) {
    let payload = xhr.responseJSON || null;
    if (!payload && xhr.responseText) {
        try { payload = JSON.parse(xhr.responseText); } catch (e) { payload = null; }
    }
    if (xhr.status === 419) return { messages: [@json(__('Your session has expired. Please refresh the page and try again.'))], errors: null };
    if (xhr.status === 403) return { messages: [(payload && payload.message) || @json(__('You do not have permission.'))], errors: null };
    if (xhr.status === 422 && payload && payload.errors) {
        const messages = [];
        Object.keys(payload.errors).forEach(function (key) {
            (payload.errors[key] || []).forEach(function (item) { messages.push(item); });
        });
        return { messages: messages.length ? messages : [@json(__('Validation failed.'))], errors: payload.errors };
    }
    if (payload && payload.message) return { messages: [payload.message], errors: payload.errors || null };
    if (xhr.status === 0) return { messages: [@json(__('Network error. Please check your connection.'))], errors: null };
    return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
}

function prodQaAppend(selectId, id, text, selectIt) {
    const el = document.getElementById(selectId);
    if (!el || id == null) return;
    const $sel = $(el);
    const value = String(id);
    const label = text || ('#' + value);
    if ($sel.find('option[value="' + value + '"]').length === 0) {
        $sel.append(new Option(label, value, !!selectIt, !!selectIt));
    } else if (selectIt) {
        $sel.val(value);
    }
    if (selectIt) $sel.trigger('change');
}

function prodQaPost(url, data) {
    prodQaMute(true);
    return $.ajax({
        url: url,
        type: 'POST',
        data: data,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).always(function () { prodQaMute(false); });
}

window.__unitQuickAddTarget = 'product_unit';

window.openQuickUnitModal = function (targetId) {
    window.__unitQuickAddTarget = targetId || 'product_unit';
    const form = document.getElementById('quickUnitForm');
    if (!form) return;
    form.reset();
    prodQaClearError('prodUnitFormError', form);
    $('#quick_unit_active').prop('checked', true);
    $('#quick_unit_conversion_rate').val(1);
    $('#quickUnitModal').modal('show');
    setTimeout(function () { $('#quick_unit_name').focus(); }, 300);
};

window.openQuickCategoryModal = function () {
    const form = document.getElementById('quickCategoryForm');
    if (!form) return;
    form.reset();
    prodQaClearError('prodCategoryFormError', form);
    $('#quick_category_active').prop('checked', true);
    $('#quick_category_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
    $('#quickCategoryModal').modal('show');
    setTimeout(function () { $('#quick_category_name').focus(); }, 300);
};

window.openQuickBrandModal = function () {
    const form = document.getElementById('quickBrandForm');
    if (!form) return;
    form.reset();
    prodQaClearError('prodBrandFormError', form);
    $('#quick_brand_active').prop('checked', true);
    $('#quick_brand_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
    $('#quickBrandModal').modal('show');
    setTimeout(function () { $('#quick_brand_name').focus(); }, 300);
};

$(document).on('submit', '#quickUnitForm', function (e) {
    e.preventDefault();
    const form = this;
    const btn = $('#quickUnitSaveBtn');
    prodQaClearError('prodUnitFormError', form);

    const formData = new FormData(form);
    formData.append('_token', '{{ csrf_token() }}');
    if (!$('#quick_unit_active').is(':checked')) formData.delete('is_active');

    btn.prop('disabled', true);
    $('#quickUnitSpinner').removeClass('d-none');

    prodQaPost("{{ route('units.store') }}", formData)
        .done(function (response) {
            if (response && response.success && response.unit) {
                const unitId = response.unit.id;
                const unitName = response.unit.name;
                const target = window.__unitQuickAddTarget || 'product_unit';

                prodQaAppend('product_unit', unitId, unitName, false);
                prodQaAppend('purchase_unit', unitId, unitName, false);
                prodQaAppend('selling_unit', unitId, unitName, false);
                prodQaAppend('quick_unit_base_unit', unitId, unitName, false);

                if (target === 'product_unit') {
                    const $pu = $('#product_unit');
                    if ($pu.length) $pu.val(String(unitId)).trigger('change');
                } else if ($('#' + target).length) {
                    $('#' + target).val(String(unitId));
                }

                $('#quickUnitModal').modal('hide');
                form.reset();
                $('#quick_unit_active').prop('checked', true);
                return;
            }
            prodQaShowError('prodUnitFormError', (response && response.message) || @json(__('Could not save unit.')), form);
        })
        .fail(function (xhr) {
            const payload = prodQaExtract(xhr);
            prodQaClearError('prodUnitFormError', form);
            if (payload.errors) prodQaMarkErrors(form, payload.errors);
            prodQaShowError('prodUnitFormError', payload.messages, form);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('#quickUnitSpinner').addClass('d-none');
        });
    return false;
});

$(document).on('submit', '#quickCategoryForm', function (e) {
    e.preventDefault();
    const form = this;
    const btn = $('#quickCategorySaveBtn');
    prodQaClearError('prodCategoryFormError', form);

    const formData = new FormData(form);
    formData.append('_token', '{{ csrf_token() }}');
    if (!$('#quick_category_active').is(':checked')) formData.delete('is_active');

    btn.prop('disabled', true);
    $('#quickCategorySpinner').removeClass('d-none');

    prodQaPost("{{ route('categories.store') }}", formData)
        .done(function (response) {
            if (response && response.success && response.category) {
                prodQaAppend('category_id', response.category.id, response.category.name, true);
                prodQaAppend('quick_category_parent', response.category.id, response.category.name, false);
                $('#quickCategoryModal').modal('hide');
                form.reset();
                $('#quick_category_active').prop('checked', true);
                $('#quick_category_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
                return;
            }
            prodQaShowError('prodCategoryFormError', (response && response.message) || @json(__('Could not save category.')), form);
        })
        .fail(function (xhr) {
            const payload = prodQaExtract(xhr);
            prodQaClearError('prodCategoryFormError', form);
            if (payload.errors) prodQaMarkErrors(form, payload.errors);
            prodQaShowError('prodCategoryFormError', payload.messages, form);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('#quickCategorySpinner').addClass('d-none');
        });
    return false;
});

$(document).on('submit', '#quickBrandForm', function (e) {
    e.preventDefault();
    const form = this;
    const btn = $('#quickBrandSaveBtn');
    prodQaClearError('prodBrandFormError', form);

    const formData = new FormData(form);
    formData.append('_token', '{{ csrf_token() }}');
    if (!$('#quick_brand_active').is(':checked')) formData.delete('is_active');

    btn.prop('disabled', true);
    $('#quickBrandSpinner').removeClass('d-none');

    prodQaPost("{{ route('brands.store') }}", formData)
        .done(function (response) {
            if (response && response.success && response.brand) {
                prodQaAppend('brand_id', response.brand.id, response.brand.name, true);
                $('#quickBrandModal').modal('hide');
                form.reset();
                $('#quick_brand_active').prop('checked', true);
                $('#quick_brand_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
                return;
            }
            prodQaShowError('prodBrandFormError', (response && response.message) || @json(__('Could not save brand.')), form);
        })
        .fail(function (xhr) {
            const payload = prodQaExtract(xhr);
            prodQaClearError('prodBrandFormError', form);
            if (payload.errors) prodQaMarkErrors(form, payload.errors);
            prodQaShowError('prodBrandFormError', payload.messages, form);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('#quickBrandSpinner').addClass('d-none');
        });
    return false;
});

$(document).on('input change', '#quickUnitForm input, #quickUnitForm select, #quickCategoryForm input, #quickCategoryForm select, #quickCategoryForm textarea, #quickBrandForm input, #quickBrandForm select, #quickBrandForm textarea', function () {
    $(this).removeClass('is-invalid');
    $(this).siblings('.prod-qa-field-error').remove();
});
</script>
@endpush
