@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create Product
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/vendors/filepond.css">
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/vendors/filepond-plugin-image-preview.css">
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/vendors/quill.snow.css">
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/vendors/tom-select/tom-select.css">
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/custom.css">
    <style>
        .ts-control {
            border: none !important;
            padding: 0px !important;
        }
        /* Quill link/video tooltip — keep visible and inside editor (not over sidebar) */
        .toolbar-box {
            overflow: visible !important;
            position: relative;
        }
        .toolbar-box .ql-container {
            overflow: visible !important;
        }
        #editor2 .ql-tooltip,
        #editor2 .ql-tooltip.ql-editing,
        #editor2 .ql-tooltip.ql-out-bottom,
        #editor2 .ql-tooltip.ql-out-top,
        #editor2 .ql-editing.ql-out-bottom,
        #editor2 .ql-editing.ql-out-top {
            visibility: visible !important;
            z-index: 20 !important;
            left: 12px !important;
            right: auto !important;
            top: 10px !important;
            transform: none !important;
            position: absolute !important;
            max-width: calc(100% - 24px);
            white-space: normal;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Create Product
        @endslot
        @slot('subtitle')
            Create new product for your inventory.
        @endslot
        <li class="breadcrumb-item active">Create Product</li>
    @endcomponent

    <form class="row layout-top-spacing g-4" id="product_form_data" action="{{ route('products.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label for="product_type">Product Type <span class="text-danger">*</span></label>
                            <select name="product_type" id="product_type" class="form-control" required>
                                <option value="1">Physical</option>
                                <option value="2">Digital</option>
                                <option value="3">Combo</option>
                                <option value="4">Service</option>
                            </select>
                            @error('product_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="product_title">Product Title <span class="text-danger">*</span></label>
                            <input type="text" name="product_title" id="product_title" class="form-control" required>
                            @error('product_title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_code">Product Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input class="form-control" id="product_code" name="product_code" type="text"
                                    placeholder="" required>
                                <button class="btn btn-primary brl_0" id="generate_product_code"
                                    type="button">Generate</button>
                            </div>
                            @error('product_code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_cost">Product Cost <span class="text-danger">*</span></label>
                            <input type="number" name="product_cost" id="product_cost" class="form-control" step="0.01" min="0" required>
                            @error('product_cost')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" id="selling_price" class="form-control" step="0.01" min="0" required>
                            @error('selling_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="wholesale_price">Wholesale Price</label>
                            <input type="number" name="wholesale_price" id="wholesale_price" class="form-control" step="0.01" min="0">
                            @error('wholesale_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_unit">Product Unit <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-1">
                                <select name="product_unit" id="product_unit" class="form-control" required style="flex: 1;">
                                    <option selected disabled value="">Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickUnitModal('product_unit')" title="Add New Unit" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @error('product_unit')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="purchase_unit">Purchase Unit</label>
                            <div class="d-flex align-items-center gap-1">
                                <select name="purchase_unit" id="purchase_unit" class="form-control" style="flex: 1;">
                                    <option selected disabled value="">Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickUnitModal('purchase_unit')" title="Add New Unit" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="selling_unit">Selling Unit</label>
                            <div class="d-flex align-items-center gap-1">
                                <select name="selling_unit" id="selling_unit" class="form-control" style="flex: 1;">
                                    <option selected disabled value="">Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickUnitModal('selling_unit')" title="Add New Unit" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label for="editor2">Product Description</label>
                        <div class="toolbar-box">
                            <div id="toolbar2"><span class="ql-formats">
                                    <select class="ql-size"></select></span><span class="ql-formats">
                                    <button class="ql-bold">Bold </button>
                                    <button class="ql-italic">Italic </button>
                                    <button class="ql-underline">underline</button>
                                    <button class="ql-strike">Strike </button></span><span class="ql-formats">
                                    <button class="ql-list" value="ordered">List </button>
                                    <button class="ql-list" value="bullet"> </button>
                                    <button class="ql-indent" value="-1"> </button>
                                    <button class="ql-indent" value="+1"></button></span><span class="ql-formats">
                                    <button class="ql-link"></button>
                                    <button class="ql-image"></button>
                                    <button class="ql-video"></button></span></div>
                            <div id="editor2"></div>
                            <input type="hidden" name="description" id="description">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 combo_items_form d-none mt-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-primary border-0 py-3">
                                <h5 class="mb-0 text-white">Combo Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="combo_product_search" style="color: black !important;">Search and Add Product to Combo <span class="text-danger">*</span></label>
                                    <select id="combo_product_search" class="form-control" placeholder="Search product by name or code..."></select>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="combo_items_table">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th style="min-width: 150px;">Product Name</th>
                                                <th style="width: 15%; min-width: 100px;">Quantity</th>
                                                <th style="width: 20%; min-width: 120px;">Unit Price</th>
                                                <th style="width: 20%; min-width: 100px;">Total Price</th>
                                                <th style="width: 10%; min-width: 60px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Combo items will be appended here -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total Combo Cost:</th>
                                                <th id="total_combo_cost">0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 hide_on_combo">
                        <div class="form-group mb-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    value="1" id="product_variation" name="is_variation">
                                <label class="form-check-label" style="font-weight: 600;" for="product_variation">
                                    Product Variation
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="has_imei" value="1" id="has_imei">
                                <label class="form-check-label" style="font-weight: 600;" for="has_imei">
                                    This product has IMEI/Serial Number
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 variant_form mb-4 d-none">
                        <div class="variant-wrapper">
                            <div class="row variant-row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="key">Variant Name</label>
                                        <input type="text" name="variant_key[]" class="form-control"
                                            placeholder="Key">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="value">Value</label>
                                        <input type="text" name="variant_value[]" class="form-control variant_value"
                                            placeholder="Value">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group mb-3">
                                        <button type="button" class="btn btn-danger remove-variant">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 text-right mt-2">
                            <button type="button" id="add_more_variant" class="btn btn-primary">Add More</button>
                            {{-- <button type="button" id="generate_variant" class="btn btn-primary">Generate</button> --}}

                        </div>
                        <div class="col-md-12 mt-2">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Variant Name</th>
                                        <th>Variant Code</th>
                                        <th title="Added to base product cost">Additional Cost (+)</th>
                                        <th title="Added to base selling price">Additional Price (+)</th>
                                        <th>Image</th>
                                    </tr>
                                </thead>
                                <tbody class="variant-list">

                                </tbody>
                            </table>

                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="has_expiry_date" id="has_expiry_date">
                                <label class="form-check-label" style="font-weight: 600;" for="has_expiry_date">
                                    This product has expiry date
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="is_feature" value="1" id="special_feature">
                                <label class="form-check-label" style="font-weight: 600;" for="special_feature">
                                    This product has special feature
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 special_features_form d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="promotional_price">Promotional Price</label>
                                    <input type="number" name="promotional_price" id="promotional_price"
                                        class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="limit">Limit</label>
                                    <input type="number" name="limit" id="limit" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="is_online" id="online_product_seo">
                                <label class="form-check-label" style="font-weight: 600;" for="online_product_seo">
                                    Online Product SEO
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 online_product_seo_form d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                                        placeholder="Meta Title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="meta_keyword">Meta Keywords</label>
                                    <input type="text" name="meta_keyword" id="meta_keyword" class="form-control"
                                        placeholder="Meta Keywords">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                                        placeholder="Meta Description"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="tags">Tags</label>
                                    <input type="text" name="tags[]" id="tags" class="form-control"
                                        placeholder="Tags">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_title">OG Title</label>
                                            <input type="text" name="og_title" id="og_title" class="form-control"
                                                placeholder="OG Title">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_url">OG URL</label>
                                            <input type="url" name="og_url" id="og_url" class="form-control"
                                                placeholder="OG URL">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_type">OG Type</label>
                                            <input type="text" name="og_type" id="og_type" class="form-control"
                                                placeholder="OG Type">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_locale">OG Locale</label>
                                            <input type="text" name="og_locale" id="og_locale" class="form-control"
                                                placeholder="OG Locale">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_description">OG Description</label>
                                            <textarea name="og_description" id="og_description" class="form-control" rows="1"
                                                placeholder="OG Description"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_image">OG Image</label>
                                            <input type="file" name="og_image" id="og_image" class="form-control"
                                                accept=".jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex align-items-center justify-content-between">
                            <label class="form-label fw-semibold mb-0" for="is_active">Status</label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="is_active" name="is_active" value="1" checked
                                    style="width: 42px; height: 22px;">
                                <label class="form-check-label ms-2" for="is_active">Active</label>
                            </div>
                        </div>
                        <hr class="mt-0 mb-3">
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <div class="d-flex align-items-center gap-1">
                                <select name="category_id" id="category_id" class="form-control" style="flex: 1;">
                                    <option selected disabled value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickCategoryModal()" title="Add New Category" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="brand_id">Brand</label>
                            <div class="d-flex align-items-center gap-1">
                                <select name="brand_id" id="brand_id" class="form-control" style="flex: 1;">
                                    <option selected disabled value="">Select Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" onclick="openQuickBrandModal()" title="Add New Brand" style="width: 34px; height: 34px; min-width: 34px; border-radius: 5px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_sell_commission">Max Sale Commission</label>
                            <div class="input-group">
                                <button class="btn btn-outline-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="max-commission-type-dropdown">{{ __('Percent') }}</span> <i
                                        class="fas fa-caret-down ms-2"></i>
                                </button>
                                <ul class="dropdown-menu max-commission-type-dropdown-block">
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="percentage">{{ __('Percent') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="fixed">{{ __('Flat') }}</a></li>
                                </ul>
                                {{-- <input type="hidden" name="comission_type" value="Percent" class="max-commission-type"> --}}
                                <input type="hidden" name="comission_type" value="percentage" class="max-commission-type">
                                <input class="form-control" name="max_comission"
                                    style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" type="text"
                                    aria-label="Text input with dropdown button">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tax">Tax </label>
                            <div class="input-group">
                                @php
                                    $defaultTax = $general_settings['default_tax'] ?? 0;
                                    $defaultTaxMethod = $defaultTax > 0 ? 'Exclusive' : 'No Tax';
                                    $defaultTaxMethodVal = $defaultTax > 0 ? 'exclusive' : 'no_tax';
                                @endphp
                                <button class="btn btn-outline-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="tax-method-dropdown">{{ __($defaultTaxMethod) }}</span> <i class="fas fa-caret-down ms-2"></i>
                                </button>
                                <ul class="dropdown-menu tax-method-dropdown-block">
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="no_tax">{{ __('No Tax') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="inclusive">{{ __('Inclusive') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="exclusive">{{ __('Exclusive') }}</a></li>
                                </ul>
                                <input type="hidden" name="tax_method" value="{{ $defaultTaxMethodVal }}" class="tax-method">
                                <input class="form-control" name="tax"
                                    style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" type="number" step="0.01"
                                    value="{{ $defaultTax > 0 ? $defaultTax : '' }}"
                                    {{ $defaultTaxMethodVal == 'no_tax' ? 'readonly' : '' }}
                                    aria-label="Text input with dropdown button" placeholder="%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: 12px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="thumbnail">{{ __('Thumbnail') }} <small class="text-muted">{{ __('(Ratio 1:1, Recommended: 500x500px)') }}</small></label>
                            <div class="pos-file">
                                <input type="file" class="pos-file-input" name="thumbnail" id="thumbnail"
                                    accept=".jpg,.jpeg,.png"
                                    onchange="if (this.files[0]) { document.getElementById('thumbnail_preview').src = window.URL.createObjectURL(this.files[0]); } posFileName(this)">
                                <span class="pos-file-btn">{{ __('Choose File') }}</span>
                                <span class="pos-file-name" data-empty="{{ __('No file chosen') }}" data-many="{{ __('images selected') }}">{{ __('No file chosen') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <img id="thumbnail_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                            class="img-fluid float-end" alt="{{ __('Thumbnail Preview') }}"
                            style="max-height: 100px; max-width: 100px;" />
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="gallery">{{ __('Gallery') }} <small class="text-muted">{{ __('(Ratio 1:1, Recommended: 500x500px)') }}</small></label>
                            <div class="pos-file">
                                <input type="file" multiple class="pos-file-input" name="gallery[]" id="gallery"
                                    accept=".jpg,.jpeg,.png" onchange="previewGalleryImages(this); posFileName(this)">
                                <span class="pos-file-btn">{{ __('Choose Files') }}</span>
                                <span class="pos-file-name" data-empty="{{ __('No file chosen') }}" data-many="{{ __('images selected') }}">{{ __('No file chosen') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row" id="gallery_preview">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-secondary me-2 submit_btn" name="submit"
                            value="draft">Draft</button>
                        <button type="submit" class="btn btn-primary submit_btn" name="submit"
                            value="save_and_publish">Save and Publish</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Quick Add Category Modal --}}
    <div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-labelledby="quickCategoryModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="quickCategoryModalTitle"><i class="fas fa-folder-plus me-2"></i>Add New Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickCategoryForm" enctype="multipart/form-data">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_category_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_category_name" name="name" placeholder="Category Name" required>
                                <div class="invalid-feedback" id="quick_category_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_category_parent" class="form-label">Parent Category</label>
                                <select class="form-control" id="quick_category_parent" name="parent_id">
                                    <option value="">Select Parent</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_category_description" class="form-label">Description</label>
                                <textarea style="caret-color: black;" class="form-control" id="quick_category_description" name="description" placeholder="Description" rows="2"></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-check-label mb-0" for="quick_category_active">Is Active</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_category_active" checked name="is_active" style="width: 45px; height: 22px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_category_image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="quick_category_image" name="image" accept=".jpg,.jpeg,.png"
                                    onchange="document.getElementById('quick_category_image_preview').src = window.URL.createObjectURL(this.files[0])">
                            </div>
                            <div class="col-12 mb-3 text-center">
                                <img id="quick_category_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                                    class="img-fluid border rounded mt-2" alt="Image Preview" style="max-height: 80px; max-width: 80px;" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickCategorySaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickCategorySpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick Add Brand Modal --}}
    <div class="modal fade" id="quickBrandModal" tabindex="-1" aria-labelledby="quickBrandModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="quickBrandModalTitle"><i class="fas fa-tags me-2"></i>Add New Brand</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickBrandForm" enctype="multipart/form-data">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_brand_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_brand_name" name="name" placeholder="Brand Name" required>
                                <div class="invalid-feedback" id="quick_brand_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_brand_description" class="form-label">Description</label>
                                <textarea style="caret-color: black;" class="form-control" id="quick_brand_description" name="description" placeholder="Description" rows="2"></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-check-label mb-0" for="quick_brand_active">Is Active</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_brand_active" checked name="is_active" style="width: 45px; height: 22px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_brand_image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="quick_brand_image" name="image" accept=".jpg,.jpeg,.png"
                                    onchange="document.getElementById('quick_brand_image_preview').src = window.URL.createObjectURL(this.files[0])">
                            </div>
                            <div class="col-12 mb-3 text-center">
                                <img id="quick_brand_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                                    class="img-fluid border rounded mt-2" alt="Image Preview" style="max-height: 80px; max-width: 80px;" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-md" id="quickBrandSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickBrandSpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick Add Unit Modal --}}
    <div class="modal fade" id="quickUnitModal" tabindex="-1" aria-labelledby="quickUnitModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="quickUnitModalTitle"><i class="fas fa-ruler-combined me-2"></i>Add New Unit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickUnitForm">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="quick_unit_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_unit_name" name="name" placeholder="Unit Name" required>
                                <div class="invalid-feedback" id="quick_unit_error"></div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_unit_short_code" class="form-label">Short Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quick_unit_short_code" name="short_code" placeholder="Short Code" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_unit_base_unit" class="form-label">Base Unit</label>
                                <select class="form-control" id="quick_unit_base_unit" name="base_unit">
                                    <option value="">Select Base Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_unit_operator" class="form-label">Operator</label>
                                <select class="form-control" id="quick_unit_operator" name="operator">
                                    <option value="">Select Operator</option>
                                    <option value="*">Multiply (*)</option>
                                    <option value="/">Divide (/)</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="quick_unit_conversion_rate" class="form-label">Conversion Rate <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="quick_unit_conversion_rate" name="conversion_rate" value="1" required>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-check-label mb-0" for="quick_unit_active">Is Active</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input switch-size-lg m-0" type="checkbox" role="switch" id="quick_unit_active" checked name="is_active" style="width: 45px; height: 22px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary btn-md" id="quickUnitSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="quickUnitSpinner" role="status"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ url('backend') }}/assets/js/filepond/filepond.js"></script>
    <script src="{{ url('backend') }}/assets/js/filepond/custom-filepond.js"></script>
    <script src="{{ url('backend') }}/assets/js/editors/quill.js"></script>
    {{-- <script src="{{ url('backend') }}/assets/js/custom-add-product.js"></script> --}}
    <script src="{{ url('backend') }}/assets/js/vendors/tom-select/tom-select.complete.min.js"></script>
    <script>
        $('#products').addClass('active');
        $('#products-submenu').addClass('d-block');
        $('.create-product-menu').addClass('active');

        // Generate Product Code
        $('#generate_product_code').on('click', function() {
            let title = $('#product_title').val().trim();
            let prefix = '';
            
            if (title.length > 0) {
                // Get up to first 3 alphanumeric characters
                let cleanTitle = title.replace(/[^a-zA-Z0-9]/g, '');
                if (cleanTitle.length > 0) {
                    prefix = cleanTitle.substring(0, 3).toUpperCase() + '-';
                }
            }

            const generateUniqueCode = () => {
                const now = new Date();
                const dayPart = String(now.getDate()).padStart(2, '0');
                const secPart = String(now.getSeconds()).padStart(2, '0');
                const randPart = Math.floor(1000 + Math.random() * 9999);
                return `${dayPart}${secPart}${randPart}`;
            };

            const uniqueCode = prefix + generateUniqueCode();
            $('#product_code').val(uniqueCode);
        });



        // Default Purchase/Selling Unit to Product Unit (keep full unit lists)
        window.__syncPurchaseSellingFromProduct = true;
        $('#product_unit').on('change', function() {
            if (!window.__syncPurchaseSellingFromProduct) return;
            let id = $(this).val();
            if (!id) return;
            if ($('#purchase_unit option[value="' + id + '"]').length) {
                $('#purchase_unit').val(id);
            }
            if ($('#selling_unit option[value="' + id + '"]').length) {
                $('#selling_unit').val(id);
            }
        });

        // Persist old input data to prevent data loss on validation failure
        @if(count(old()) > 0)
            const oldData = @json(old());
            window.__syncPurchaseSellingFromProduct = false;
            for (const key in oldData) {
                if(oldData.hasOwnProperty(key)) {
                    let input = $(`[name="${key}"]`);
                    if(input.length) {
                        if(input.is(':checkbox') || input.is(':radio')) {
                            input.filter(`[value="${oldData[key]}"]`).prop('checked', true);
                            if(oldData[key] == 1 || oldData[key] == 'on') input.prop('checked', true).trigger('change');
                        } else {
                            input.val(oldData[key]).trigger('change');
                        }
                    }
                }
            }
            if (oldData.purchase_unit) $('#purchase_unit').val(oldData.purchase_unit);
            if (oldData.selling_unit) $('#selling_unit').val(oldData.selling_unit);
            window.__syncPurchaseSellingFromProduct = true;
        @endif

        $(document).on('click', '.max-commission-type-dropdown-block a', function() {
            var dbValue = $(this).attr('data-value') || 'percentage';
            $('.max-commission-type-dropdown').text($(this).text());
            $('.max-commission-type').val(dbValue);
        });

        $(document).on('click', '.tax-method-dropdown-block a', function() {
            var dbValue = $(this).attr('data-value') || 'no_tax';
            $('.tax-method-dropdown').text($(this).text());
            $('.tax-method').val(dbValue);
            if (dbValue === 'no_tax') {
                $('input[name="tax"]').prop('readonly', true).val('');
            } else {
                $('input[name="tax"]').prop('readonly', false);
            }
        });

        $('#product_variation').on('change', function() {
            if ($(this).is(':checked')) {
                $('.variant_form').removeClass('d-none');
                $('.variant_form .form-control').attr('required', true);
            } else {
                $('.variant_form').addClass('d-none');
                $('.variant_form .form-control').attr('required', false);
            }
        });

        //Special Feature
        $('#special_feature').on('change', function() {
            if ($(this).is(':checked')) {
                $('.special_features_form').removeClass('d-none');
                $('.special_features_form .form-control').attr('required', true);
            } else {
                $('.special_features_form').addClass('d-none');
                $('.special_features_form .form-control').attr('required', false);
            }
        });

        $('#online_product_seo').on('change', function() {
            if ($(this).is(':checked')) {
                $('.online_product_seo_form').removeClass('d-none');
            } else {
                $('.online_product_seo_form').addClass('d-none');
            }
        });

        // Combo Product Logic
        $('#product_type').on('change', function() {
            if ($(this).val() == '3') { // Combo
                $('.hide_on_combo').addClass('d-none');
                $('.combo_items_form').removeClass('d-none');
                $('#product_cost').prop('readonly', true);
            } else {
                $('.hide_on_combo').removeClass('d-none');
                $('.combo_items_form').addClass('d-none');
                $('#product_cost').prop('readonly', false);
            }
        });

        // Init Combo TomSelect
        let comboSelect = new TomSelect('#combo_product_search', {
            valueField: 'id',
            labelField: 'text',
            searchField: ['text', 'code'],
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    url: "{{ route('products.searchForCombo') }}",
                    type: 'GET',
                    data: { q: query },
                    success: function(res) {
                        callback(res);
                    },
                    error: function() {
                        callback();
                    }
                });
            },
            onChange: function(value) {
                if (!value) return;
                let data = this.options[value];
                addComboItem(data);
                this.clear(); // Clear input
            }
        });

        function addComboItem(data) {
            // Check if already added
            if ($(`#combo_row_${data.id}`).length > 0) {
                let qtyInput = $(`#combo_row_${data.id} .combo-qty`);
                qtyInput.val(parseFloat(qtyInput.val()) + 1);
                updateComboTotals();
                return;
            }

            let tr = `
                <tr id="combo_row_${data.id}">
                    <td>
                        ${data.text}
                        <input type="hidden" name="combo_product_ids[]" value="${data.product_id}">
                        <input type="hidden" name="combo_variant_ids[]" value="${data.variant_id || ''}">
                    </td>
                    <td>
                        <input type="number" name="combo_quantities[]" class="form-control combo-qty" value="1" min="1" step="any" onchange="updateComboTotals()" onkeyup="updateComboTotals()">
                    </td>
                    <td>
                        <input type="number" name="combo_unit_prices[]" class="form-control combo-price" value="${data.price}" step="any" readonly>
                        <input type="hidden" class="combo-cost" value="${data.cost}">
                    </td>
                    <td>
                        <span class="combo-row-total">${data.price}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').remove(); updateComboTotals();">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#combo_items_table tbody').append(tr);
            updateComboTotals();
        }

        function updateComboTotals() {
            let totalCost = 0;
            let totalPrice = 0;

            $('#combo_items_table tbody tr').each(function() {
                let qty = parseFloat($(this).find('.combo-qty').val()) || 0;
                let price = parseFloat($(this).find('.combo-price').val()) || 0;
                let cost = parseFloat($(this).find('.combo-cost').val()) || 0;

                let rowTotal = qty * price;
                $(this).find('.combo-row-total').text(rowTotal.toFixed(2));

                totalPrice += rowTotal;
                totalCost += (qty * cost);
            });

            $('#total_combo_cost').text(totalCost.toFixed(2));
            $('#product_cost').val(totalCost.toFixed(2));
            // Automatically suggest selling price, user can still edit it
            if ($('#product_type').val() == '3') {
                $('#selling_price').val(totalPrice.toFixed(2));
            }
        }

        new TomSelect('#tags', {
            persist: false,
            createOnBlur: true,
            create: true
        });
        // Add More Variant
        $('#add_more_variant').click(function() {
            let newRow = $(`
                <div class="row variant-row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="key">Variant Name</label>
                            <input type="text" name="variant_key[]" class="form-control" placeholder="Key">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label for="value">Value</label>
                            <input type="text" name="variant_value[]" class="form-control variant_value" placeholder="Value">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-group mb-3">
                            <button type="button" class="btn btn-danger remove-variant">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);

            $('.variant-row:last').after(newRow);

            new TomSelect(newRow.find('.variant_value')[0], {
                persist: false,
                createOnBlur: true,
                create: true
            });
        });

        $(document).on('click', '.remove-variant', function() {
            if ($('.variant-row').length > 1) {
                $(this).closest('.variant-row').remove();
            }
        });

        new TomSelect('.variant_value', {
            persist: false,
            createOnBlur: true,
            create: true
        });
        // Generate variant combinations
        function generateVariants() {
            let values = [];

            // values only (not keys)
            $('input[name="variant_value[]"]').each(function() {
                let valStr = $(this).val().trim();
                if (valStr !== '') {
                    let valArr = valStr.split(',').map(v => v.trim()).filter(v => v !== '');
                    values.push(valArr);
                }
            });

            // Generate combinations
            function cartesianProduct(arrays) {
                return arrays.reduce((acc, curr) => {
                    let result = [];
                    acc.forEach(a => {
                        curr.forEach(b => {
                            result.push(a.concat([b]));
                        });
                    });
                    return result;
                }, [
                    []
                ]);
            }

            let combos = cartesianProduct(values);
            let productCode = $('#product_code').val();
            // create inputs
            let variantInputs = '';
            combos.forEach((combo, i) => {
                let name = combo.join('-');
                variantInputs += `
                    <tr>
                        <td><input type="text" name="variant_name[]" class="form-control" value="${name}" required></td>
                        <td><input type="text" name="variant_code[]" class="form-control" value="${productCode}-${name}" required></td>
                        <td><input type="number" name="additional_cost[]" class="form-control" value="0" step="0.01" min="0" title=@json(__('Amount added to base cost')) required></td>
                        <td><input type="number" name="additional_price[]" class="form-control" value="0" step="0.01" min="0" title=@json(__('Amount added to base selling price')) required></td>
                        <td><input type="file" name="variant_image[]" class="form-control"></td>
                    </tr>
                `;
            });

            $('.variant-list').html(variantInputs);
        }


        $(document).on('input', 'input[name="variant_value[]"]', function() {
            generateVariants();
        });

        // Gallery
        let selectedFiles = [];

        function previewGalleryImages(input) {
            const previewContainer = document.getElementById('gallery_preview');
            previewContainer.innerHTML = '';
            selectedFiles = Array.from(input.files); 

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3 position-relative';

                    col.innerHTML = `
                        <div class="position-relative border p-2 rounded">
                            <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 150px;" />
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeGalleryImage(${index})">&times;</button>
                        </div>
                    `;

                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });

            updateFileList(input);
        }

        function removeGalleryImage(index) {
            selectedFiles.splice(index, 1); 
            const input = document.getElementById('gallery');
            updateFileList(input);
            previewGalleryImages(input);
        }

        function updateFileList(input) {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
            posFileName(input);
        }

        function posFileName(input) {
            var box = input.closest('.pos-file');
            if (!box) return;
            var nameEl = box.querySelector('.pos-file-name');
            if (!nameEl) return;
            if (!input.files || input.files.length === 0) {
                nameEl.textContent = nameEl.getAttribute('data-empty') || '';
                return;
            }
            if (input.files.length === 1) {
                nameEl.textContent = input.files[0].name;
                return;
            }
            nameEl.textContent = input.files.length + ' ' + (nameEl.getAttribute('data-many') || '');
        }

        var quill2 = new Quill('#editor2', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: '#toolbar2',
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });

        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                const res = await fetch("{{ route('products.upload-quill-image') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();

                if (data.url) {
                    const range = quill2.getSelection(true);
                    quill2.insertEmbed(range.index, 'image', data.url);
                } else {
                    showFloatingAlert('danger', @json(__('Image upload failed')));
                }
            };
        }

        // Custom Toaster Function matching alert.blade.php
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        document.querySelector('#product_form_data').onsubmit = function() {
            const html = quill2.root.innerHTML;
            document.getElementById('description').value = html;
        };

        // Quick Add Category Modal
        let quickCategoryModalInstance;
        $(document).ready(function() {
            const modalEl = document.getElementById('quickCategoryModal');
            if (modalEl) {
                quickCategoryModalInstance = new bootstrap.Modal(modalEl);
            }
        });

        function openQuickCategoryModal() {
            // Reset all fields
            $('#quickCategoryForm')[0].reset();
            $('#quick_category_name').removeClass('is-invalid');
            $('#quick_category_error').text('');
            $('#quick_category_active').prop('checked', true);
            $('#quick_category_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
            quickCategoryModalInstance.show();
            setTimeout(() => $('#quick_category_name').focus(), 300);
        }

        $('#quickCategoryForm').on('submit', function(e) {
            e.preventDefault();

            const name = $('#quick_category_name').val().trim();
            if (!name) {
                $('#quick_category_name').addClass('is-invalid');
                $('#quick_category_error').text(@json(__('Category name is required.')));
                return;
            }

            $('#quickCategorySaveBtn').prop('disabled', true);
            $('#quickCategorySpinner').removeClass('d-none');
            $('#quick_category_name').removeClass('is-invalid');

            // Use FormData for file upload support
            var formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            // Handle is_active checkbox properly
            if (!$('#quick_category_active').is(':checked')) {
                formData.delete('is_active');
            }

            $.ajax({
                url: "{{ route('categories.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Add new category to the product form dropdown and select it
                        const newOption = new Option(response.category.name, response.category.id, true, true);
                        $('#category_id').append(newOption);

                        // Also add to the parent dropdown in the modal for future use
                        const parentOption = new Option(response.category.name, response.category.id, false, false);
                        $('#quick_category_parent').append(parentOption);

                        quickCategoryModalInstance.hide();

                        // Show a brief success toast/alert
                        showFloatingAlert('success', response.message || @json(__('Category created successfully!')));
                    } else {
                        showFloatingAlert('danger', response.message || @json(__('Failed to create category.')));
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('#quick_category_name').addClass('is-invalid');
                            $('#quick_category_error').text(errors.name[0]);
                        }
                    } else {
                        $('#quick_category_name').addClass('is-invalid');
                        $('#quick_category_error').text(@json(__('Failed to create category. Please try again.')));
                    }
                },
                complete: function() {
                    $('#quickCategorySaveBtn').prop('disabled', false);
                    $('#quickCategorySpinner').addClass('d-none');
                },
            });
        });

        // Quick Add Brand Modal
        let quickBrandModalInstance;
        $(document).ready(function() {
            const brandModalEl = document.getElementById('quickBrandModal');
            if (brandModalEl) {
                quickBrandModalInstance = new bootstrap.Modal(brandModalEl);
            }
        });

        function openQuickBrandModal() {
            $('#quickBrandForm')[0].reset();
            $('#quick_brand_name').removeClass('is-invalid');
            $('#quick_brand_error').text('');
            $('#quick_brand_active').prop('checked', true);
            $('#quick_brand_image_preview').attr('src', '{{ url("backend/assets/images/preview_image.png") }}');
            quickBrandModalInstance.show();
            setTimeout(() => $('#quick_brand_name').focus(), 300);
        }

        $('#quickBrandForm').on('submit', function(e) {
            e.preventDefault();

            const name = $('#quick_brand_name').val().trim();
            if (!name) {
                $('#quick_brand_name').addClass('is-invalid');
                $('#quick_brand_error').text(@json(__('Brand name is required.')));
                return;
            }

            $('#quickBrandSaveBtn').prop('disabled', true);
            $('#quickBrandSpinner').removeClass('d-none');
            $('#quick_brand_name').removeClass('is-invalid');

            var formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            if (!$('#quick_brand_active').is(':checked')) {
                formData.delete('is_active');
            }

            $.ajax({
                url: "{{ route('brands.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const newOption = new Option(response.brand.name, response.brand.id, true, true);
                        $('#brand_id').append(newOption);

                        quickBrandModalInstance.hide();
                        showFloatingAlert('success', response.message || @json(__('Brand ":name" created!')).replace(':name', response.brand.name));
                    } else {
                        showFloatingAlert('danger', response.message || @json(__('Failed to create brand.')));
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('#quick_brand_name').addClass('is-invalid');
                            $('#quick_brand_error').text(errors.name[0]);
                        }
                    } else {
                        $('#quick_brand_name').addClass('is-invalid');
                        $('#quick_brand_error').text(@json(__('Failed to create brand. Please try again.')));
                    }
                },
                complete: function() {
                    $('#quickBrandSaveBtn').prop('disabled', false);
                    $('#quickBrandSpinner').addClass('d-none');
                },
            });
        });

        // Quick Add Unit Modal
        let quickUnitModalInstance;
        $(document).ready(function() {
            const unitModalEl = document.getElementById('quickUnitModal');
            if (unitModalEl) {
                quickUnitModalInstance = new bootstrap.Modal(unitModalEl);
            }
        });

        window.__unitQuickAddTarget = 'product_unit';
        function openQuickUnitModal(targetId) {
            window.__unitQuickAddTarget = targetId || 'product_unit';
            $('#quickUnitForm')[0].reset();
            $('#quick_unit_name').removeClass('is-invalid');
            $('#quick_unit_error').text('');
            $('#quick_unit_active').prop('checked', true);
            $('#quick_unit_conversion_rate').val(1);
            quickUnitModalInstance.show();
            setTimeout(() => $('#quick_unit_name').focus(), 300);
        }

        $('#quickUnitForm').on('submit', function(e) {
            e.preventDefault();

            const name = $('#quick_unit_name').val().trim();
            if (!name) {
                $('#quick_unit_name').addClass('is-invalid');
                $('#quick_unit_error').text(@json(__('Unit name is required.')));
                return;
            }

            $('#quickUnitSaveBtn').prop('disabled', true);
            $('#quickUnitSpinner').removeClass('d-none');
            $('#quick_unit_name').removeClass('is-invalid');

            var formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            if (!$('#quick_unit_active').is(':checked')) {
                formData.delete('is_active');
            }

            $.ajax({
                url: "{{ route('units.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const unitId = String(response.unit.id);
                        const unitName = response.unit.name;
                        const target = window.__unitQuickAddTarget || 'product_unit';

                        ['product_unit', 'purchase_unit', 'selling_unit', 'quick_unit_base_unit'].forEach(function (selId) {
                            const $sel = $('#' + selId);
                            if (!$sel.length) return;
                            if ($sel.find('option[value="' + unitId + '"]').length === 0) {
                                $sel.append(new Option(unitName, unitId, false, false));
                            }
                        });

                        if (target === 'product_unit') {
                            $('#product_unit').val(unitId).trigger('change');
                        } else {
                            $('#' + target).val(unitId);
                        }

                        quickUnitModalInstance.hide();
                        showFloatingAlert('success', response.message || @json(__('Unit ":name" created!')).replace(':name', unitName));
                    } else {
                        showFloatingAlert('danger', response.message || @json(__('Failed to create unit.')));
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('#quick_unit_name').addClass('is-invalid');
                            $('#quick_unit_error').text(errors.name[0]);
                        }
                    } else {
                        $('#quick_unit_name').addClass('is-invalid');
                        $('#quick_unit_error').text(@json(__('Failed to create unit. Please try again.')));
                    }
                },
                complete: function() {
                    $('#quickUnitSaveBtn').prop('disabled', false);
                    $('#quickUnitSpinner').addClass('d-none');
                },
            });
        });
    </script>
@endpush
