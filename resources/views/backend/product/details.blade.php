<style>
    .product-img-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 250px;
        border: 1px solid #e9ecef;
    }

    .product-price {
        font-size: 1.75rem;
        font-weight: 700;
        color: #198754;
    }

    .info-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #212529;
    }

    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        border: none;
        border-bottom: 2px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: transparent;
    }

    .table-variants th {
        background-color: #f1f3f5;
        font-size: 0.85rem;
    }

    #desc,
    #desc * {
        color: #000 !important;
    }

    /* Mobile Responsive Fixes */
    @media (max-width: 575.98px) {
        .product-img-container {
            padding: 10px;
            min-height: 180px;
        }
        .product-price {
            font-size: 1.4rem !important;
            word-break: break-all;
        }
        .info-label {
            font-size: 0.75rem !important;
        }
        .info-value {
            font-size: 0.9rem !important;
        }
        .badge.fs-6 {
            font-size: 0.8rem !important;
            padding: 0.35rem 0.5rem !important;
            white-space: normal;
            line-height: 1.2;
        }
        .nav-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem !important;
            padding-bottom: 0 !important;
        }
        .nav-tabs .nav-item {
            flex: 0 0 auto;
        }
        .nav-tabs .nav-link {
            padding: 0.5rem 1rem;
            white-space: nowrap;
        }
        .nav-tabs::-webkit-scrollbar {
            display: none !important;
        }
        .table-variants th, .table-variants td {
            white-space: nowrap;
        }
        .tab-content .p-3 {
            padding: 15px 10px !important;
        }
    }
</style>

<div class="row g-4">
    <div class="col-md-5">
        <div class="product-img-container shadow-sm">
            <img src="{{ $product->thumbnail ? asset('storage/products/' . $product->thumbnail) : asset('backend/assets/images/preview_image.png') }}"
                class="img-fluid rounded" style="max-height: 250px; object-fit: contain;">
        </div>
    </div>

    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $product->product_title }}</h4>
                <div class="mb-3">
                    <span class="badge bg-primary text-light border">Code: {{ $product->product_code }}</span>
                    <span
                        class="badge bg-info bg-opacity-10 text-info border border-info">{{ $product->category->name ?? 'Uncategorized' }}</span>
                    <span
                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $product->brand->name ?? 'No Brand' }}</span>
                </div>
            </div>
        </div>

        <div class="p-3 bg-light rounded border mb-3">
            <div class="row text-center">
                <div class="col-6 border-end">
                    <div class="info-label">Selling Price</div>
                    <div class="product-price">{{ \App\Helpers\CurrencyHelper::format($product->selling_price, 2) }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">Total Stock</div>
                    <div class="mt-1">
                        @php $stock = $product->productBranches->sum('qty'); @endphp
                        @if ($stock > 0)
                            <span class="badge bg-success fs-6">{{ $stock }}
                                {{ $product->unit->name ?? '' }}</span>
                        @else
                            <span class="badge bg-danger fs-6">Out of Stock</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <div class="info-label">Purchase Cost</div>
                <div class="info-value">{{ \App\Helpers\CurrencyHelper::format($product->product_cost, 2) }}</div>
            </div>
            <div class="col-6">
                <div class="info-label">Unit</div>
                <div class="info-value">{{ $product->unit->name ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button"
                role="tab">Description</button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button"
                role="tab">
                Branch Stock <span class="badge bg-success bg-opacity-10 text-success ms-1"><i
                        class="fas fa-store"></i></span>
            </button>
        </li>

        @if ($product->is_variation && $product->productVariants->count() > 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button"
                    role="tab">Variants <span
                        class="badge bg-secondary rounded-pill ms-1">{{ $product->productVariants->count() }}</span></button>
            </li>
        @endif

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button"
                role="tab">SEO Info</button>
        </li>
    </ul>

    <div class="tab-content" id="productTabContent">
        <div class="tab-pane fade show active" id="desc" role="tabpanel">
            <div class="p-3 bg-light rounded border">
                {!! clean($product->description) !!}
            </div>
        </div>

        <div class="tab-pane fade" id="stock" role="tabpanel">
            <div class="table-responsive border rounded">
                <table class="table table-hover table-variants mb-0 text-center align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-start ps-4">Branch Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Total Available Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hasBranchStock = false;
                            $groupedBranches = $product->productBranches->groupBy('branch_id');
                        @endphp

                        @foreach ($groupedBranches as $branchId => $pBranches)
                            @php
                                $totalQty = $pBranches->sum('qty');
                                $firstItem = $pBranches->first();
                            @endphp

                            @if ($totalQty > 0)
                                @php $hasBranchStock = true; @endphp
                                <tr>
                                    <td class="text-start ps-4 fw-bold text-primary">
                                        {{ $firstItem->branch->name ?? 'Unknown' }}
                                    </td>
                                    <td>{{ $firstItem->branch->phone ?? '-' }}</td>
                                    <td><small
                                            class="text-muted">{{ Str::limit($firstItem->branch->address ?? '-', 30) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            {{ $totalQty }} {{ $product->unit->name ?? '' }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        @if (!$hasBranchStock)
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">
                                    <i class="fas fa-box-open mb-1"></i><br>
                                    No stock available in any branch.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if ($product->is_variation && $product->productVariants->count() > 0)
            <div class="tab-pane fade" id="variants" role="tabpanel">
                <div class="table-responsive border rounded">
                    <table class="table table-hover table-variants mb-0 text-center align-middle">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Variant Name</th>
                                <th>Code</th>
                                <th>Add. Cost</th>
                                <th>Add. Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($product->productVariants as $variant)
                                <tr>
                                    <td>
                                        <img src="{{ $variant->image ? asset('storage/products/variants/' . $variant->image) : asset('backend/assets/images/preview_image.png') }}"
                                            width="30" class="rounded">
                                    </td>
                                    <td class="fw-bold">{{ $variant->variant_name }}</td>
                                    <td><span class="badge bg-primary text-light border">{{ $variant->variant_code }}</span>
                                    </td>
                                    <td>{{ \App\Helpers\CurrencyHelper::format($variant->additional_cost, 2) }}</td>
                                    <td class="text-success fw-bold">
                                        +{{ \App\Helpers\CurrencyHelper::format($variant->additional_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="tab-pane fade" id="seo" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="info-label">Meta Title</label>
                    <p class="small text-dark">{{ $product->seo->meta_title ?? 'N/A' }}</p>
                </div>
                <div class="col-md-12">
                    <label class="info-label">Meta Description</label>
                    <p class="small text-dark">{{ $product->seo->meta_description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>