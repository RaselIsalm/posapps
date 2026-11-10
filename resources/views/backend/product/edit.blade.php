@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Edit Product
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
            {{ trans('Edit Product') }}
        @endslot
        @slot('subtitle')
            Edit product details.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ trans('Products') }}</a></li>
        <li class="breadcrumb-item active">{{ trans('Edit Product') }}</li>
    @endcomponent

    <form class="row layout-top-spacing g-4" id="product_form_data" action="{{ route('products.update', $product->id) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label for="product_type">Product Type <span class="text-danger">*</span></label>
                            <select name="product_type" id="product_type" class="form-control" required>
                                <option value="1" {{ $product->product_type == '1' ? 'selected' : '' }}>Physical
                                </option>
                                <option value="2" {{ $product->product_type == '2' ? 'selected' : '' }}>Digital
                                </option>
                                <option value="3" {{ $product->product_type == '3' ? 'selected' : '' }}>Combo</option>
                                <option value="4" {{ $product->product_type == '4' ? 'selected' : '' }}>Service
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="product_title">Product Title <span class="text-danger">*</span></label>
                            <input type="text" name="product_title" id="product_title" class="form-control"
                                value="{{ $product->product_title }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_code">Product Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input class="form-control" id="product_code" name="product_code" type="text"
                                    value="{{ $product->product_code }}" required>
                                <button class="btn btn-primary brl_0" id="generate_product_code"
                                    type="button">Generate</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Price Section --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_cost">Product Cost <span class="text-danger">*</span></label>
                            <input type="number" name="product_cost" id="product_cost" class="form-control"
                                value="{{ $product->product_cost }}" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" id="selling_price" class="form-control"
                                value="{{ $product->selling_price }}" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="wholesale_price">Wholesale Price</label>
                            <input type="number" name="wholesale_price" id="wholesale_price" class="form-control"
                                value="{{ $product->wholesale_price }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                {{-- Unit Section --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="product_unit">Product Unit <span class="text-danger">*</span></label>
                            <div class="prod-qa-field-group">
                                <select name="product_unit" id="product_unit" class="form-control" required>
                                    <option value="" selected disabled>Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $product->product_unit == $unit->id ? 'selected' : '' }}>{{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_units')
                                    <button type="button" class="btn btn-outline-primary btn-prod-qa-add"
                                        onclick="openQuickUnitModal('product_unit')" title="Add New Unit" aria-label="Add New Unit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="purchase_unit">Purchase Unit</label>
                            <div class="prod-qa-field-group">
                                <select name="purchase_unit" id="purchase_unit" class="form-control">
                                    <option value="" disabled {{ empty($product->purchase_unit) && empty($product->product_unit) ? 'selected' : '' }}>Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ ($product->purchase_unit ?: $product->product_unit) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_units')
                                    <button type="button" class="btn btn-outline-primary btn-prod-qa-add"
                                        onclick="openQuickUnitModal('purchase_unit')" title="Add New Unit" aria-label="Add New Unit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="selling_unit">Selling Unit</label>
                            <div class="prod-qa-field-group">
                                <select name="selling_unit" id="selling_unit" class="form-control">
                                    <option value="" disabled {{ empty($product->selling_unit) && empty($product->product_unit) ? 'selected' : '' }}>Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ ($product->selling_unit ?: $product->product_unit) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_units')
                                    <button type="button" class="btn btn-outline-primary btn-prod-qa-add"
                                        onclick="openQuickUnitModal('selling_unit')" title="Add New Unit" aria-label="Add New Unit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="row">
                    <div class="col-12">
                        <label for="editor2">Product Description</label>
                        <div class="toolbar-box">
                            <div id="toolbar2">
                                <span class="ql-formats">
                                    <select class="ql-size"></select>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-bold">Bold </button>
                                    <button class="ql-italic">Italic </button>
                                    <button class="ql-underline">underline</button>
                                    <button class="ql-strike">Strike </button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered">List </button>
                                    <button class="ql-list" value="bullet"> </button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-link"></button>
                                    <button class="ql-image"></button>
                                    <button class="ql-video"></button>
                                </span>
                            </div>
                            <div id="editor2">{!! clean($product->description) !!}</div>
                            <input type="hidden" name="description" id="description"
                                    value="{!! clean($product->description) !!}">
                        </div>
                    </div>
                </div>

                {{-- Variation Checkbox --}}
                {{-- Combo Items Section --}}
                <div class="row">
                    <div class="col-md-12 combo_items_form {{ $product->product_type == 3 ? '' : 'd-none' }} mt-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-primary border-0 py-3">
                                <h5 class="mb-0 text-white">Combo Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="combo_product_search">Search and Add Product to Combo <span class="text-danger">*</span></label>
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
                                            @if($product->product_type == 3 && $product->comboItems)
                                                @foreach($product->comboItems as $comboItem)
                                                    @php
                                                        $uniqueId = $comboItem->combo_item_id . ($comboItem->variant_id ? '-'.$comboItem->variant_id : '');
                                                        $itemName = $comboItem->comboItem->product_title;
                                                        if($comboItem->variant) {
                                                            $itemName .= ' - ' . $comboItem->variant->item_code;
                                                        }
                                                        // Fallback cost if variant/product not loaded correctly
                                                        $itemCost = $comboItem->variant ? ($comboItem->comboItem->product_cost + $comboItem->variant->additional_cost) : $comboItem->comboItem->product_cost;
                                                    @endphp
                                                    <tr id="combo_row_{{ $uniqueId }}">
                                                        <td>
                                                            {{ $itemName }}
                                                            <input type="hidden" name="combo_product_ids[]" value="{{ $comboItem->combo_item_id }}">
                                                            <input type="hidden" name="combo_variant_ids[]" value="{{ $comboItem->variant_id ?? '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="combo_quantities[]" class="form-control combo-qty" value="{{ $comboItem->quantity }}" min="1" step="any" onchange="updateComboTotals()" onkeyup="updateComboTotals()">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="combo_unit_prices[]" class="form-control combo-price" value="{{ $comboItem->unit_price }}" step="any" readonly>
                                                            <input type="hidden" class="combo-cost" value="{{ $itemCost }}">
                                                        </td>
                                                        <td>
                                                            <span class="combo-row-total">{{ \App\Helpers\CurrencyHelper::format($comboItem->total_price, 2) }}</span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').remove(); updateComboTotals();">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
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

                    <div class="col-md-12 hide_on_combo {{ $product->product_type == 3 ? 'd-none' : '' }}">
                        <div class="form-group mb-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    value="1" id="product_variation" name="is_variation"
                                    {{ $product->is_variation == 1 ? 'checked' : '' }}>
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

                    {{-- Variation Form --}}
                    <div class="col-md-12 variant_form mb-4 {{ $product->is_variation == 0 ? 'd-none' : '' }}">
                        <div class="variant-wrapper">
                            @if ($product->options && $product->options->count() > 0)
                                @foreach ($product->options as $option)
                                    <div class="row variant-row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="key">Variant Name</label>
                                                <input type="text" name="variant_key[]" class="form-control"
                                                    value="{{ $option->name }}" placeholder="Key (e.g. Size)">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                <label for="value">Value</label>
                                                <input type="text" name="variant_value[]"
                                                    class="form-control variant_value"
                                                    value="{{ $option->values->pluck('value')->implode(',') }}"
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
                                @endforeach
                            @else
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
                                            <input type="text" name="variant_value[]"
                                                class="form-control variant_value" placeholder="Value">
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="form-group mb-3">
                                            <button type="button" class="btn btn-danger remove-variant"><i
                                                    class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-12 text-right mt-2">
                            <button type="button" id="add_more_variant" class="btn btn-primary">Add More</button>
                        </div>

                        {{-- Variation Table --}}
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
                                    @foreach ($product->productVariants as $item)
                                        <tr>
                                            <td><input type="text" name="variant_name[]" class="form-control"
                                                    value="{{ $item->variant_name }}" readonly></td>
                                            <td><input type="text" name="variant_code[]" class="form-control"
                                                    value="{{ $item->variant_code }}" required></td>
                                            <td><input type="number" name="additional_cost[]" class="form-control"
                                                    value="{{ $item->additional_cost }}" step="0.01" min="0" title="Amount added to base cost" required></td>
                                            <td><input type="number" name="additional_price[]" class="form-control"
                                                    value="{{ $item->additional_price }}" step="0.01" min="0" title="Amount added to base selling price" required></td>
                                            <td>
                                                <input type="file" name="variant_image[]" class="form-control">
                                                @if ($item->image)
                                                    <a href="{{ asset('storage/products/variants/' . $item->image) }}"
                                                        target="_blank" class="text-primary text-xs">View Image</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Extra Features --}}
                <div class="row hide_on_combo {{ $product->product_type == 3 ? 'd-none' : '' }}">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="has_expiry_date" id="has_expiry_date"
                                    {{ $product->has_expiry_date == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-weight: 600;" for="has_expiry_date">This
                                    product has expiry date</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="is_feature" value="1" id="special_feature"
                                    {{ $product->is_feature == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-weight: 600;" for="special_feature">This
                                    product has special feature</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 special_features_form {{ $product->is_feature == 1 ? '' : 'd-none' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="promotional_price">Promotional Price</label>
                                    <input type="number" name="promotional_price" id="promotional_price"
                                        class="form-control" step="0.01" value="{{ $product->promotional_price }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="{{ $product->start_date }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="{{ $product->end_date }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="limit">Limit</label>
                                    <input type="number" name="limit" id="limit" class="form-control"
                                        value="{{ $product->limit }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEO Section --}}
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" style="transform: scale(1.3);" type="checkbox"
                                    name="is_online" id="online_product_seo"
                                    {{ $product->is_online_seo == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-weight: 600;" for="online_product_seo">Online
                                    Product SEO</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 online_product_seo_form {{ $product->is_online_seo == 1 ? '' : 'd-none' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                                        value="{{ $product->seo ? $product->seo->meta_title : '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="meta_keyword">Meta Keywords</label>
                                    <input type="text" name="meta_keyword" id="meta_keyword" class="form-control"
                                        value="{{ $product->seo ? $product->seo->meta_keywords : '' }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" class="form-control" rows="3">{{ $product->seo ? $product->seo->meta_description : '' }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="tags">Tags</label>
                                    <input type="text" name="tags[]" id="tags" class="form-control"
                                        value="{{ $product->tags }}">
                                </div>
                            </div>
                            {{-- OG Fields --}}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_title">OG Title</label>
                                            <input type="text" name="og_title" class="form-control"
                                                value="{{ $product->seo ? $product->seo->og_title : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_url">OG URL</label>
                                            <input type="url" name="og_url" class="form-control"
                                                value="{{ $product->seo ? $product->seo->og_url : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_type">OG Type</label>
                                            <input type="text" name="og_type" class="form-control"
                                                value="{{ $product->seo ? $product->seo->og_type : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_locale">OG Locale</label>
                                            <input type="text" name="og_locale" class="form-control"
                                                value="{{ $product->seo ? $product->seo->og_locale : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_description">OG Description</label>
                                            <textarea name="og_description" class="form-control" rows="1">{{ $product->seo ? $product->seo->og_description : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="og_image">OG Image</label>
                                            <input type="file" name="og_image" class="form-control"
                                                accept=".jpg,.jpeg,.png">
                                            @if ($product->seo && $product->seo->og_image)
                                                <img src="{{ asset('storage/products/og_images/' . $product->seo->og_image) }}"
                                                    class="img-thumbnail mt-2" width="100">
                                            @endif
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
                                    id="is_active" name="is_active" value="1"
                                    {{ $product->is_active == 1 ? 'checked' : '' }}
                                    style="width: 42px; height: 22px;"
                                    onchange="document.querySelector('label[for=is_active].form-check-label').textContent = this.checked ? 'Active' : 'Inactive';">
                                <label class="form-check-label ms-2" for="is_active">
                                    {{ $product->is_active == 1 ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                        </div>
                        <hr class="mt-0 mb-3">
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <div class="prod-qa-field-group">
                                <select name="category_id" id="category_id" class="form-control">
                                    <option selected disabled value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @can('create_categories')
                                    <button type="button" class="btn btn-outline-primary btn-prod-qa-add"
                                        onclick="openQuickCategoryModal()" title="Add New Category" aria-label="Add New Category">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="brand_id">Brand</label>
                            <div class="prod-qa-field-group">
                                <select name="brand_id" id="brand_id" class="form-control">
                                    <option selected disabled value="">Select Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_brands')
                                    <button type="button" class="btn btn-outline-primary btn-prod-qa-add"
                                        onclick="openQuickBrandModal()" title="Add New Brand" aria-label="Add New Brand">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_sell_commission">Max Sale Commission</label>
                            <div class="input-group">
                                <button class="btn btn-outline-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="max-commission-type-dropdown">
                                        @if (($product->comission_type ?? 'percentage') === 'flat')
                                            {{ __('Flat') }}
                                        @else
                                            {{ __('Percent') }}
                                        @endif
                                    </span>
                                    <i class="fas fa-caret-down ms-2"></i>
                                </button>
                                <ul class="dropdown-menu max-commission-type-dropdown-block">
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="percentage">{{ __('Percent') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="flat">{{ __('Flat') }}</a></li>
                                </ul>
                                <input type="hidden" name="comission_type"
                                    value="{{ $product->comission_type ?? 'percentage' }}" class="max-commission-type">
                                <input class="form-control" name="max_comission"
                                    style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" type="text"
                                    value="{{ $product->max_comission ?? 0 }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tax">Tax </label>
                            <div class="input-group">
                                <button class="btn btn-outline-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="tax-method-dropdown">
                                        @php $taxMethod = $product->tax_method ?? 'no_tax'; @endphp
                                        @if ($taxMethod === 'inclusive')
                                            {{ __('Inclusive') }}
                                        @elseif ($taxMethod === 'exclusive')
                                            {{ __('Exclusive') }}
                                        @else
                                            {{ __('No Tax') }}
                                        @endif
                                    </span> <i
                                        class="fas fa-caret-down ms-2"></i>
                                </button>
                                <ul class="dropdown-menu tax-method-dropdown-block">
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="no_tax">{{ __('No Tax') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="inclusive">{{ __('Inclusive') }}</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-value="exclusive">{{ __('Exclusive') }}</a></li>
                                </ul>
                                <input type="hidden" name="tax_method" class="tax-method"
                                    value="{{ $product->tax_method ?? 'no_tax' }}">
                                <input class="form-control" name="tax"
                                    style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" type="text"
                                    value="{{ $product->tax ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Image Card --}}
            <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: 12px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="thumbnail">{{ __('Thumbnail') }} <small class="text-muted">{{ __('(Ratio 1:1, Recommended: 500x500px)') }}</small></label>
                            <div class="pos-file">
                                <input type="file" class="pos-file-input" name="thumbnail" id="thumbnail"
                                    accept=".jpg,.jpeg,.png"
                                    onchange="if (this.files[0]) { document.getElementById('remove_thumbnail').value='0'; document.getElementById('thumbnail_preview').src = window.URL.createObjectURL(this.files[0]); var btn=document.getElementById('removeThumbnailBtn'); if(btn) btn.style.display=''; } posFileName(this)">
                                <span class="pos-file-btn">{{ __('Choose File') }}</span>
                                <span class="pos-file-name" data-empty="{{ __('No file chosen') }}" data-many="{{ __('images selected') }}">{{ __('No file chosen') }}</span>
                            </div>
                            <input type="hidden" name="remove_thumbnail" id="remove_thumbnail" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        @php
                            $thumbnailPlaceholder = url('backend/assets/images/preview_image.png');
                        @endphp
                        <img id="thumbnail_preview"
                            src="{{ $product->thumbnail ? asset('storage/products/' . $product->thumbnail) : $thumbnailPlaceholder }}"
                            data-placeholder="{{ $thumbnailPlaceholder }}"
                            class="img-fluid float-end" alt="{{ __('Thumbnail Preview') }}"
                            style="max-height: 100px; max-width: 100px;" />
                        @if ($product->thumbnail)
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" id="removeThumbnailBtn">Remove image</button>
                            </div>
                        @endif
                        <script>
                            (function () {
                                var flag = document.getElementById('remove_thumbnail');
                                var img = document.getElementById('thumbnail_preview');
                                var file = document.getElementById('thumbnail');
                                var btn = document.getElementById('removeThumbnailBtn');
                                if (btn) {
                                    btn.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        if (flag) flag.value = '1';
                                        if (file) file.value = '';
                                        if (img) img.src = img.getAttribute('data-placeholder') || img.src;
                                        posFileName(file);
                                        btn.hidden = true;
                                        btn.style.setProperty('display', 'none', 'important');
                                    });
                                }
                            })();
                        </script>
                    </div>
                </div>

                {{-- Gallery Images --}}
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
                        {{-- Existing Gallery Images --}}
                        <div class="row mb-2">
                            @if ($product->media)
                                @foreach ($product->media as $media)
                                    <div class="col-md-3 mb-2" id="media_card_{{ $media->id }}">
                                        <div class="border p-1 rounded position-relative">
                                            <img src="{{ asset('storage/products/gallery/' . $media->image) }}" class="img-fluid rounded"
                                                style="height: 60px; width: 100%; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1" onclick="deleteExistingMedia({{ $media->id }})">
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        {{-- New Upload Preview --}}
                        <div class="row" id="gallery_preview"></div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-secondary me-2 submit_btn" name="submit"
                            value="draft">Draft</button>
                        <button type="submit" class="btn btn-primary submit_btn" name="submit"
                            value="save_and_publish">Update Product</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @include('backend.product.partials.quick_add_unit_category_brand')
@endsection

@push('js')
    <script src="{{ url('backend') }}/assets/js/filepond/filepond.js"></script>
    <script src="{{ url('backend') }}/assets/js/filepond/custom-filepond.js"></script>
    <script src="{{ url('backend') }}/assets/js/editors/quill.js"></script>
    <script src="{{ url('backend') }}/assets/js/vendors/tom-select/tom-select.complete.min.js"></script>
    <script>
        $('#products').addClass('active');
        $('#products-submenu').addClass('d-block');

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

        // Generate Product Code (Same as create)
        $('#generate_product_code').on('click', function() {
            let title = $('#product_title').val().trim();
            let prefix = '';
            
            if (title.length > 0) {
                let cleanTitle = title.replace(/[^a-zA-Z0-9]/g, '');
                if (cleanTitle.length > 0) {
                    prefix = cleanTitle.substring(0, 3).toUpperCase() + '-';
                }
            }

            const generateUniqueCode = () => {
                const timestamp = Date.now().toString().slice(-6);
                const random = Math.floor(1000 + Math.random() * 9000);
                return `${timestamp}${random}`;
            };
            $('#product_code').val(prefix + generateUniqueCode());
        });

        // Dropdowns JS
        $(document).on('click', '.max-commission-type-dropdown-block a', function() {
            $('.max-commission-type-dropdown').text($(this).text());
            $('.max-commission-type').val($(this).attr('data-value') || 'percentage');
        });

        $(document).on('click', '.tax-method-dropdown-block a', function() {
            $('.tax-method-dropdown').text($(this).text());
            $('.tax-method').val($(this).attr('data-value') || 'no_tax');
        });

        // Toggle Sections
        $('#product_variation').on('change', function() {
            if ($(this).is(':checked')) {
                $('.variant_form').removeClass('d-none');
            } else {
                $('.variant_form').addClass('d-none');
            }
        });

        $('#special_feature').on('change', function() {
            if ($(this).is(':checked')) {
                $('.special_features_form').removeClass('d-none');
            } else {
                $('.special_features_form').addClass('d-none');
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

        // Trigger on load
        if ($('#product_type').val() == '3') {
            $('#product_cost').prop('readonly', true);
            updateComboTotals();
        }

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

        // TomSelect Initialization
        new TomSelect('#tags', {
            persist: false,
            createOnBlur: true,
            create: true
        });

        document.querySelectorAll('.variant_value').forEach(function(el) {
            new TomSelect(el, {
                persist: false,
                createOnBlur: true,
                create: true
            });
        });

        // Add More Variant Row
        $('#add_more_variant').click(function() {
            let newRow = $(`
                <div class="row variant-row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Variant Name</label>
                            <input type="text" name="variant_key[]" class="form-control" placeholder="Key">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label>Value</label>
                            <input type="text" name="variant_value[]" class="form-control variant_value" placeholder="Value">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-group mb-3">
                            <button type="button" class="btn btn-danger remove-variant"><i class="fas fa-trash-alt"></i></button>
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
                generateVariants();
            }
        });

        // Generate variant combinations Logic
        function generateVariants() {
            let values = [];
            $('input[name="variant_value[]"]').each(function() {
                let valStr = $(this).val().trim();
                if (valStr !== '') {
                    let valArr = valStr.split(',').map(v => v.trim()).filter(v => v !== '');
                    values.push(valArr);
                }
            });

            if (values.length === 0) return;

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
            let variantInputs = '';

            combos.forEach((combo, i) => {
                let name = combo.join('-');
                variantInputs += `
                    <tr>
                        <td><input type="text" name="variant_name[]" class="form-control" value="${name}" readonly></td>
                        <td><input type="text" name="variant_code[]" class="form-control" value="${productCode}-${name}" required></td>
                        <td><input type="number" name="additional_cost[]" class="form-control" value="0" step="0.01" min="0" title=@json(__('Amount added to base cost')) required></td>
                        <td><input type="number" name="additional_price[]" class="form-control" value="0" step="0.01" min="0" title=@json(__('Amount added to base selling price')) required></td>
                        <td><input type="file" name="variant_image[]" class="form-control"></td>
                    </tr>
                `;
            });
            $('.variant-list').html(variantInputs);
        }

        $(document).on('change', 'input[name="variant_value[]"]', function() {
            generateVariants();
        });

        // Gallery Preview Logic
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
            if (!input) return;
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

        function deleteExistingMedia(id) {
            let btn = $(`#media_card_${id} button`);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                url: `/products/media/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        $(`#media_card_${id}`).remove();
                        showFloatingAlert('success', res.message);
                    } else {
                        btn.prop('disabled', false).html('&times;');
                        showFloatingAlert('danger', res.message || @json(__('Failed to remove image')));
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('&times;');
                    showFloatingAlert('danger', @json(__('Something went wrong while removing image')));
                }
            });
        }

        // Quill Editor
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
    </script>
@endpush
