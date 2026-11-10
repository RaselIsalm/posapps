@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Bulk Product Import
@endsection

@push('css')
    <style>
        .card {
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: visible !important;
        }

        .import-hint {
            background: #f8f9fa;
            border: 1px dashed #dce0e4;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 13px;
            color: #6c757d;
        }

        .import-hint code {
            font-size: 12px;
            color: #495057;
        }

        .result-table thead th {
            background-color: #f8f9fa;
            color: #5b626b;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Bulk Product Import
        @endslot
        @slot('subtitle')
            Import products from Excel using the exact sample template.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Bulk Import</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">Bulk Product Import</h4>
                        <a href="{{ route('products.import.sample') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Download Sample File
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label fw-semibold">
                                    Branch <span class="text-danger">*</span>
                                </label>
                                <div class="imp-field-group">
                                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_branches')
                                        <button type="button" class="btn btn-outline-primary btn-imp-add"
                                            onclick="openImpBranchModal()" title="Add New Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                                @error('branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Opening stock (quantity column) will be added to this branch.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="excel_file" class="form-label fw-semibold">
                                    Upload Excel File <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="excel_file" id="excel_file"
                                    class="form-control @error('excel_file') is-invalid @enderror"
                                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                    required>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Accepted: .xlsx / .xls — max 5MB, max 1000 rows.</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Excel masters (create before import)</label>
                                <small class="text-muted d-block mb-2">
                                    Excel <code>category_name</code>, <code>brand_name</code>, <code>unit_name</code> must match these. Use + to add if missing.
                                </small>
                            </div>
                            <div class="col-md-4">
                                <label for="product_unit" class="form-label small fw-semibold text-muted">Unit</label>
                                <div class="imp-field-group prod-qa-field-group">
                                    <select id="product_unit" class="form-select" aria-label="Units available for import">
                                        <option value="">Units in system</option>
                                        @foreach ($units ?? [] as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_units')
                                        <button type="button" class="btn btn-outline-primary btn-imp-add btn-prod-qa-add"
                                            onclick="openQuickUnitModal()" title="Add New Unit">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label small fw-semibold text-muted">Category</label>
                                <div class="imp-field-group prod-qa-field-group">
                                    <select id="category_id" class="form-select" aria-label="Categories available for import">
                                        <option value="">Categories in system</option>
                                        @foreach ($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_categories')
                                        <button type="button" class="btn btn-outline-primary btn-imp-add btn-prod-qa-add"
                                            onclick="openQuickCategoryModal()" title="Add New Category">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="brand_id" class="form-label small fw-semibold text-muted">Brand</label>
                                <div class="imp-field-group prod-qa-field-group">
                                    <select id="brand_id" class="form-select" aria-label="Brands available for import">
                                        <option value="">Brands in system</option>
                                        @foreach ($brands ?? [] as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_brands')
                                        <button type="button" class="btn btn-outline-primary btn-imp-add btn-prod-qa-add"
                                            onclick="openQuickBrandModal()" title="Add New Brand">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="import-hint mt-4">
                            <div class="fw-semibold text-dark mb-1">Import rules</div>
                            <ul class="mb-0 ps-3">
                                <li>Headers must match the sample file exactly (same names and order).</li>
                                <li>Wrong/extra/missing columns will reject the whole file.</li>
                                <li>Invalid data rows are skipped; valid rows are imported (error report below).</li>
                                <li>Use + above to create Branch / Unit / Category / Brand if the database is empty. Excel names must match exactly.</li>
                                <li><code>product_type</code>: physical / digital / service (combo not supported).</li>
                                <li><code>quantity</code> is optional — if &gt; 0, stock is set on the selected branch.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import me-1"></i> Import Products
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                Back to Product List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 mt-4 mt-xl-0">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="m-0 fw-bold text-dark">Required columns</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <p class="text-muted small mb-2">Must be present with valid data:</p>
                    <code class="d-block mb-3" style="font-size: 12px; white-space: normal;">
                        product_title, product_code, product_type, product_cost, selling_price, category_name, brand_name, unit_name
                    </code>
                    <p class="text-muted small mb-2">Optional:</p>
                    <code class="d-block" style="font-size: 12px; white-space: normal;">
                        wholesale_price, quantity, tax, tax_method, description, is_active
                    </code>
                </div>
            </div>
        </div>
    </div>

    @if (session('import_result'))
        @php $result = session('import_result'); @endphp
        <div class="row layout-top-spacing mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white py-4 border-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h4 class="m-0 fw-bold text-dark">Import Result</h4>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success px-3 py-2">Imported: {{ $result['imported'] ?? 0 }}</span>
                                <span class="badge bg-danger px-3 py-2">Failed: {{ $result['failed'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @if (! empty($result['errors']))
                            <div class="table-responsive" style="max-height: 360px;">
                                <table class="table table-hover result-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">Row #</th>
                                            <th style="width: 160px;">Product Code</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($result['errors'] as $error)
                                            <tr>
                                                <td>{{ $error['row'] }}</td>
                                                <td>{{ $error['product_code'] }}</td>
                                                <td>{{ $error['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">All rows imported successfully.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('backend.product.partials.quick_add_import_branch')
    @include('backend.product.partials.quick_add_unit_category_brand')
@endsection

@push('js')
    <script>
        $('#products-menu').addClass('active');
        $('#products-menu-submenu').addClass('d-block');
    </script>
@endpush
