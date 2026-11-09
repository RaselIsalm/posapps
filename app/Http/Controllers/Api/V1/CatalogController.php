<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ImageHelper;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductImei;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use RuntimeException;
use Throwable;

class CatalogController extends ApiController
{
    public function products(Request $request): JsonResponse
    {
        $this->requirePermission('view_products');
        $branchIds = $this->accessibleBranches($request->user())->select('id');
        $query = Product::query()->with(['category.parent', 'brand', 'sellingUnit', 'unit', 'productBranches' => fn ($q) => $q->whereIn('branch_id', $branchIds)])
            ->where('is_active', 1)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('product_title', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                });
            });

        return $this->success($this->page($request, $query->orderBy('product_title')->orderBy('id'), fn (Product $product) => $this->productData($product), 'products'));
    }

    public function productSearch(Request $request): JsonResponse
    {
        $this->requirePermission('view_products', 'view_quick_sale', 'create_quick_sale');
        $branchId = $this->resolveBranchId($request, true);
        $query = Product::query()->with(['category', 'brand', 'sellingUnit', 'productBranches', 'productVariants.productBranches'])
            ->where('is_active', 1)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('product_title', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->limit(100);

        $products = $query->get()->map(function (Product $product) use ($branchId) {
            $stocks = $product->productBranches;
            if ($branchId) {
                $stocks = $stocks->where('branch_id', $branchId);
            }
            $quantity = (int) floor((float) $stocks->sum('qty'));
            $price = (float) ($stocks->firstWhere('branch_id', $branchId)?->sell_price ?: $product->promotional_price ?: $product->selling_price);

            $variants = $product->productVariants->map(function ($variant) use ($branchId, $product) {
                $branchStocks = $variant->productBranches;
                if ($branchId) {
                    $branchStocks = $branchStocks->where('branch_id', $branchId);
                }
                $variantPrice = (float) ($branchStocks->firstWhere('branch_id', $branchId)?->sell_price
                    ?: $variant->sellingPriceFromBase($product->selling_price));

                return [
                    'id' => (int) $variant->id,
                    'name' => $variant->variant_name,
                    'code' => $variant->variant_code,
                    'price' => $variantPrice,
                    'cost' => (float) $variant->costFromBase($product->product_cost),
                    'stock' => (int) floor((float) $branchStocks->sum('qty')),
                ];
            })->values();

            return [
                'id' => $product->id,
                'name' => $product->product_title,
                'code' => $product->product_code,
                'qty' => 1,
                'stock' => $quantity,
                'thumbnail' => ImageHelper::publicUrl($product->thumbnail, 'products'),
                'ending_date' => $product->end_date ? substr((string) $product->end_date, 0, 10) : null,
                'price' => $price,
                'cost' => (float) $product->product_cost,
                'discount' => 0.0,
                'tax' => (float) ($product->tax ?? 0),
                'subtotal' => $price,
                'batch' => (bool) $product->has_expiry_date,
                'has_imei' => (bool) $product->has_imei,
                'is_variation' => (bool) $product->is_variation,
                'variants' => $variants,
            ];
        })->values();

        return $this->success(['products' => $products]);
    }

    public function availableImeis(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $branchId = $this->resolveBranchId($request, true);
        $product = Product::with('productVariants')->findOrFail($id);
        abort_unless((bool) $product->has_imei, 422, 'This product is not IMEI tracked.');

        $data = $request->validate(['variant_id' => ['nullable', 'integer']]);
        $variantId = isset($data['variant_id']) ? (int) $data['variant_id'] : null;
        if ($variantId !== null) {
            abort_unless((bool) $product->is_variation, 422, 'This product has no variants.');
            abort_unless($product->productVariants->contains('id', $variantId), 422, 'Variant does not belong to this product.');
        }

        $imeis = ProductImei::query()
            ->where('product_id', $product->id)->where('branch_id', $branchId)
            ->where('status', 'in_stock')->where('is_sold', 0)
            ->where('variant_id', $variantId)->orderBy('imei_number')->limit(500)
            ->get(['id', 'imei_number', 'condition', 'warranty_end_date']);

        return $this->success(['imeis' => $imeis->map(fn (ProductImei $imei) => [
            'id' => (int) $imei->id,
            'imei_number' => $imei->imei_number,
            'condition' => $imei->condition,
            'warranty_end_date' => $imei->warranty_end_date
                ? substr((string) $imei->warranty_end_date, 0, 10)
                : null,
        ])->values()]);
    }

    public function productDetails(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_products');
        $branchIds = $this->accessibleBranches($request->user())->select('id');
        $product = Product::with(['category', 'brand', 'sellingUnit', 'productBranches' => fn ($q) => $q->whereIn('branch_id', $branchIds)])->findOrFail($id);

        return $this->success(['product' => $this->productData($product)]);
    }

    public function productInfo(): JsonResponse
    {
        $this->requirePermission('create_products');
        $categories = Category::active()->with('parent')->withCount('products')->orderBy('name')->get();
        $brands = Brand::active()->withCount('products')->orderBy('name')->get();

        return $this->success([
            'categories' => $categories->map(fn (Category $category) => $this->categoryData($category)),
            'brands' => $brands->map(fn (Brand $brand) => $this->brandData($brand)),
            'units' => Unit::active()->orderBy('name')->get()->map(fn (Unit $unit) => ['id' => $unit->id, 'name' => $unit->name]),
            'taxs' => [['id' => 0, 'name' => 'No tax', 'rate' => 0.0]],
            'barcodeSymbologyes' => ['CODE128', 'EAN13'],
            'productTypes' => ['Standard', 'Service'],
            'taxMethods' => ['No Tax', 'Exclusive', 'Inclusive'],
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $this->requirePermission('create_products');
        $request->merge([
            'product_title' => $request->input('product_title', $request->input('name')),
            'product_code' => $request->input('product_code', $request->input('code')),
            'product_type' => $this->productType($request->input('product_type', $request->input('type'))),
            'product_cost' => $request->input('product_cost', $request->input('cost', 0)),
            'selling_price' => $request->input('selling_price', $request->input('price')),
            'comission_type' => $request->input('comission_type', 'fixed'),
            'max_comission' => $request->input('max_comission', 0),
            'tax_method' => $request->input('tax_method', 'no_tax'),
            'product_unit' => $request->input('product_unit', $request->input('unit_id')),
            'selling_unit' => $request->input('selling_unit', $request->input('sale_unit_id')),
            'purchase_unit' => $request->input('purchase_unit', $request->input('purchase_unit_id')),
            'tax' => $request->input('tax', 0),
            'is_active' => $request->input('is_active', true),
            'thumbnail' => $request->input('thumbnail', $request->input('image')),
            'promotional_price' => $request->input('promotional_price', $request->input('promotion_price')),
            'limit' => $request->input('limit', $request->input('alert_quantity', 0)),
            'start_date' => $request->input('start_date', $request->input('starting_date')),
            'end_date' => $request->input('end_date', $request->input('last_date')),
            'has_expiry_date' => $request->input('has_expiry_date', $request->input('is_batch', false)),
            'is_feature' => $request->input('is_feature', $request->input('featured', false)),
            'is_variation' => $request->input('is_variation', $request->input('variant', false)),
            'has_imei' => $request->input('has_imei', false),
        ]);

        $this->mapVariantPayload($request);

        // Reuse the website product workflow, including image storage and activity logging.
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        if ($request->hasFile('image')) {
            $request->files->set('thumbnail', $request->file('image'));
        }
        $request->merge(['is_active' => 1]);

        $response = app(ProductController::class)->store($request);
        if ($response->isRedirect()) {
            $product = Product::query()->where('product_code', $request->input('product_code'))->first();
            if (! $product) {
                return response()->json([
                    'message' => session('error', 'Product could not be created.'),
                ], 422);
            }
            if ($product && $request->filled('alert_quantity')) {
                $product->forceFill(['alert_quantity' => (float) $request->input('alert_quantity')])->save();
            }

            return $this->success(['id' => $product?->id], 'Product created successfully.', 201);
        }

        return $response;
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_products');
        $product = Product::findOrFail($id);
        if (! auth()->user()->hasAnyRole(['Super-Admin', 'Admin']) && (int) $product->created_by !== (int) auth()->id()) {
            abort(403, 'You can only update products you created.');
        }
        abort_if((int) $product->product_type === 3, 422, 'Combo products must be edited from the website.');
        $request->merge([
            'product_title' => $request->input('product_title', $request->input('name', $product->product_title)),
            'product_code' => $request->input('product_code', $request->input('code', $product->product_code)),
            'product_type' => $this->productType($request->input('product_type', $request->input('type', $product->product_type))),
            'product_cost' => $request->input('product_cost', $request->input('cost', $product->product_cost)),
            'selling_price' => $request->input('selling_price', $request->input('price', $product->selling_price)),
            'product_unit' => $request->input('product_unit', $request->input('unit_id', $product->product_unit)),
            'purchase_unit' => $request->input('purchase_unit', $product->purchase_unit),
            'selling_unit' => $request->input('selling_unit', $product->selling_unit),
            'limit' => $request->input('limit', $request->input('alert_quantity', $product->alert_quantity)),
            'wholesale_price' => $request->input('wholesale_price', $product->wholesale_price),
            'is_active' => $request->boolean('is_active', (bool) $product->is_active),
            'is_variation' => $request->boolean('is_variation', (bool) $product->is_variation),
            'has_imei' => $request->boolean('has_imei', (bool) $product->has_imei),
            'has_expiry_date' => $request->boolean('has_expiry_date', (bool) $product->has_expiry_date),
            'is_feature' => $request->boolean('is_feature', (bool) $product->is_feature),
            'is_online' => $request->boolean('is_online', (bool) $product->is_online_seo),
            'tax_method' => $request->input('tax_method', $product->tax_method),
            'tax' => $request->input('tax', $product->tax),
            'promotional_price' => $request->input('promotional_price', $product->promotional_price),
            'start_date' => $request->input('start_date', $product->start_date),
            'end_date' => $request->input('end_date', $product->end_date),
            'tags' => $request->input('tags', $product->tags ? explode(',', (string) $product->tags) : []),
            'meta_title' => $request->input('meta_title', $product->seo?->meta_title),
            'meta_description' => $request->input('meta_description', $product->seo?->meta_description),
            'meta_keyword' => $request->input('meta_keyword', $product->seo?->meta_keywords),
            'og_title' => $request->input('og_title', $product->seo?->og_title),
            'og_description' => $request->input('og_description', $product->seo?->og_description),
            'og_url' => $request->input('og_url', $product->seo?->og_url),
            'og_locale' => $request->input('og_locale', $product->seo?->og_locale),
            'og_type' => $request->input('og_type', $product->seo?->og_type),
        ]);
        $this->mapVariantPayload($request, $id);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(ProductController::class)->update($request, $id);
        if ($response->isRedirect()) {
            return $this->success(['product' => $this->productData($this->productForUser($id, $request->user()))], 'Product updated.');
        }
        return $response instanceof JsonResponse ? $response : $this->success(['product' => $this->productData($this->productForUser($id, $request->user()))], 'Product updated.');
    }

    public function importProducts(Request $request, ProductImportService $importService): JsonResponse
    {
        $this->requirePermission('create_products');
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);
        $branchId = (int) $data['branch_id'];
        abort_unless($this->accessibleBranches($request->user())->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');
        try {
            $result = $importService->import($request->file('excel_file'), $branchId);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'The Excel file could not be read. Use the supplied sample template and try again.'], 422);
        }
        return $this->success($result, $result['imported'].' product(s) imported; '.$result['failed'].' row(s) failed.');
    }

    public function productImportSample(ProductImportService $importService)
    {
        $this->requirePermission('create_products');

        return $importService->downloadSample();
    }

    public function generateBarcodes(Request $request): JsonResponse
    {
        $this->requirePermission('view_print_barcode', 'view_products');
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        abort_if(collect($data['items'])->sum('quantity') > 500, 422, 'A print job can contain at most 500 labels.');
        $generator = new BarcodeGeneratorPNG;
        $labels = [];
        $accessibleBranchIds = $this->accessibleBranches($request->user())->pluck('id');
        foreach ($data['items'] as $item) {
            $product = Product::with('productVariants')
                ->whereHas('productBranches', fn ($query) => $query->whereIn('branch_id', $accessibleBranchIds))
                ->findOrFail($item['product_id']);
            $variant = ! empty($item['variant_id']) ? $product->productVariants->firstWhere('id', (int) $item['variant_id']) : null;
            abort_if(! empty($item['variant_id']) && ! $variant, 422, 'Variant does not belong to the selected product.');
            $code = (string) ($variant?->variant_code ?: $variant?->item_code ?: $product->product_code);
            abort_if($code === '', 422, 'Product has no barcode value.');
            $labels[] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->product_title.($variant ? ' - '.($variant->variant_name ?: $variant->item_code) : ''),
                'code' => $code,
                'price' => (float) ($variant?->sellingPriceFromBase($product->selling_price) ?? $product->selling_price),
                'quantity' => (int) $item['quantity'],
                'barcode_png_base64' => base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)),
            ];
        }
        return $this->success(['labels' => $labels]);
    }

    public function deleteProduct(int $id): JsonResponse
    {
        $this->requirePermission('delete_products');
        $product = Product::findOrFail($id);
        if (! auth()->user()->hasAnyRole(['Super-Admin', 'Admin']) && (int) $product->created_by !== (int) auth()->id()) {
            abort(403, 'You can only delete products you created.');
        }
        if ($product->productSales()->exists() || $product->productBranches()->where('qty', '>', 0)->exists()) {
            return response()->json(['message' => 'This product has stock or sales history and cannot be deleted.'], 409);
        }

        $product->deleted_by = auth()->id();
        $product->save();
        $product->delete();

        return $this->success([], 'Product deleted successfully.');
    }

    public function categories(Request $request): JsonResponse
    {
        $this->requirePermission('view_categories', 'view_products');
        $query = Category::query()->with('parent')->withCount('products')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));

        return $this->success($this->page($request, $query->orderBy('name')->orderBy('id'), fn (Category $category) => $this->categoryData($category), 'categories'));
    }

    public function parentCategories(): JsonResponse
    {
        $this->requirePermission('view_categories', 'create_categories', 'view_products', 'create_products', 'view_quick_sale', 'create_quick_sale');

        return $this->success(['categories' => Category::active()->orderBy('name')->get(['id', 'name'])]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->requirePermission('create_categories');
        $request->merge(['is_active' => 'on']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(CategoryController::class)->store($request);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Category created successfully.', 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_categories');
        $request->merge(['is_active' => 'on']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(CategoryController::class)->update($request, $id);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Category updated successfully.');
    }

    public function deleteCategory(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_categories');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(CategoryController::class)->destroy($request, $id);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Category deleted successfully.');
    }

    public function brands(Request $request): JsonResponse
    {
        $this->requirePermission('view_brands', 'view_products');
        $query = Brand::query()->withCount('products')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));

        return $this->success($this->page($request, $query->orderBy('name')->orderBy('id'), fn (Brand $brand) => $this->brandData($brand), 'brands'));
    }

    public function storeBrand(Request $request): JsonResponse
    {
        $this->requirePermission('create_brands');
        $request->merge([
            'name' => $request->input('name', $request->input('title')),
            'is_active' => 'on',
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(BrandController::class)->store($request);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Brand created successfully.', 201);
    }

    public function updateBrand(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_brands');
        $request->merge([
            'name' => $request->input('name', $request->input('title')),
            'is_active' => 'on',
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(BrandController::class)->update($request, $id);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Brand updated successfully.');
    }

    public function deleteBrand(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_brands');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $response = app(BrandController::class)->destroy($id);

        return $response instanceof JsonResponse ? $response : $this->success([], 'Brand deleted successfully.');
    }

    public function warehouses(Request $request): JsonResponse
    {
        $this->requirePermission('view_branches');
        $query = $this->accessibleBranches($request->user())
            ->withCount('purchases')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));

        return $this->success($this->page($request, $query->orderBy('name')->orderBy('id'), fn (Branch $branch) => $this->branchData($branch), 'warehouses'));
    }

    public function storeWarehouse(Request $request): JsonResponse
    {
        $this->requirePermission('create_branches');
        $account = Account::active()->where('is_default', 1)->first() ?? Account::active()->orderBy('id')->first();
        if (! $account) {
            return response()->json(['message' => 'Create an active account on the website before adding a branch.'], 422);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $branch = Branch::create([
            ...$validated,
            'manager_id' => auth()->id(),
            'account_id' => $account->id,
            'created_by' => auth()->id(),
            'status' => true,
        ]);

        return $this->success($this->branchData($branch), 'Branch created successfully.', 201);
    }

    public function updateWarehouse(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_branches');
        $branch = $this->accessibleBranches($request->user())->findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
        ]);
        $branch->fill($validated)->save();

        return $this->success($this->branchData($branch->fresh()), 'Branch updated successfully.');
    }

    public function deleteWarehouse(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_branches');
        $branch = $this->accessibleBranches($request->user())->findOrFail($id);
        if ($branch->sales()->exists() || $branch->purchases()->exists()) {
            return response()->json(['message' => 'This branch has sales or purchases and cannot be deleted.'], 409);
        }
        $branch->delete();

        return $this->success([], 'Branch deleted successfully.');
    }

    public function branchesForPos(Request $request): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('status', 1)->first();
        $branches = $employee
            ? Branch::query()->whereKey($employee->branch_id)->where('status', 1)->get(['id', 'name'])
            : $this->accessibleBranches($request->user())->orderBy('name')->get(['id', 'name']);

        return $this->success([
            'branches' => $branches,
            'locked_branch_id' => $employee?->branch_id,
            'locked_seller_id' => $employee?->id,
        ]);
    }

    public function accessibleBranchesForUser(User $user)
    {
        return $this->accessibleBranches($user);
    }

    private function accessibleBranches(User $user)
    {
        $employee = Employee::query()->where('user_id', $user->id)->where('status', 1)->first();
        if ($employee) {
            return Branch::query()->whereKey($employee->branch_id)->where('status', 1);
        }
        if ($user->hasAnyRole(['Super-Admin', 'Admin']) || $user->can('view_branches')) {
            return Branch::query()->where('status', 1);
        }

        return Branch::query()->where('manager_id', $user->id)->where('status', 1);
    }

    private function resolveBranchId(Request $request, bool $required): ?int
    {
        $user = $request->user();
        $employee = Employee::query()->where('user_id', $user->id)->where('status', 1)->first();
        if ($employee) {
            abort_unless($employee->branch_id, 422, 'Your employee profile has no branch assigned. Contact an administrator.');

            return (int) $employee->branch_id;
        }

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            abort_unless($this->accessibleBranches($user)->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');

            return $branchId;
        }

        $branches = $this->accessibleBranches($user)->orderBy('id')->get(['id']);
        if ($branches->count() === 1) {
            return (int) $branches->first()->id;
        }
        if ($required) {
            abort(422, 'Select a branch before completing a sale.');
        }

        return null;
    }

    private function productData(Product $product): array
    {
        $product->loadMissing('productVariants');
        $quantity = (int) floor((float) $product->productBranches->sum('qty'));
        $price = (float) ($product->promotional_price ?: $product->selling_price);

        return [
            'id' => (int) $product->id,
            'name' => $product->product_title,
            'code' => $product->product_code,
            'category_id' => $product->category_id ? (int) $product->category_id : null,
            'brand_id' => $product->brand_id ? (int) $product->brand_id : null,
            'unit_id' => $product->product_unit ? (int) $product->product_unit : null,
            'purchase_unit_id' => $product->purchase_unit ? (int) $product->purchase_unit : null,
            'selling_unit_id' => $product->selling_unit ? (int) $product->selling_unit : null,
            'thumbnail' => ImageHelper::publicUrl($product->thumbnail, 'products'),
            'brand' => $product->brand?->name,
            'category' => $product->category?->name,
            'unit' => $product->sellingUnit?->name ?? $product->unit?->name,
            'price' => $price,
            'qty' => $quantity,
            'stock' => $quantity,
            'cost' => (float) $product->product_cost,
            'wholesale_price' => $product->wholesale_price !== null ? (float) $product->wholesale_price : null,
            'discount' => 0.0,
            'tax' => (float) ($product->tax ?? 0),
            'tax_method' => $product->tax_method,
            'subtotal' => $price,
            'batch' => (bool) $product->has_expiry_date,
            'has_expiry_date' => (bool) $product->has_expiry_date,
            'has_imei' => (bool) $product->has_imei,
            'is_variation' => (bool) $product->is_variation,
            'is_feature' => (bool) $product->is_feature,
            'is_active' => (bool) $product->is_active,
            'is_online' => (bool) $product->is_online_seo,
            'promotional_price' => $product->promotional_price !== null ? (float) $product->promotional_price : null,
            'start_date' => $product->start_date ? substr((string) $product->start_date, 0, 10) : null,
            'ending_date' => $product->end_date ? substr((string) $product->end_date, 0, 10) : null,
            'alert_qty' => (int) ($product->alert_quantity ?? 0),
            'product_type' => match ((int) $product->product_type) {
                2 => 'Service', 3 => 'Combo', default => 'Standard'
            },
            'purchase_cost' => (float) $product->product_cost,
            'description' => $product->description,
            'variants' => $product->productVariants->map(fn ($variant) => [
                'id' => (int) $variant->id,
                'name' => $variant->variant_name,
                'code' => $variant->variant_code,
                'additional_cost' => (float) ($variant->additional_cost ?? 0),
                'additional_price' => (float) ($variant->additional_price ?? 0),
            ])->values(),
        ];
    }

    private function productForUser(int $id, User $user): Product
    {
        $branchIds = $this->accessibleBranches($user)->select('id');

        return Product::query()->with([
            'category',
            'brand',
            'sellingUnit',
            'unit',
            'productBranches' => fn ($query) => $query->whereIn('branch_id', $branchIds),
            'productVariants',
        ])->findOrFail($id);
    }

    private function mapVariantPayload(Request $request, ?int $productId = null): void
    {
        $variants = $request->input('variants');
        if ($variants === null) {
            $request->merge(['variant_key' => []]);
            return;
        }
        abort_unless(is_array($variants), 422, 'Variants must be a list.');
        $request->validate([
            'variants' => ['array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.code' => ['nullable', 'string', 'max:100'],
            'variants.*.additional_cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.additional_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        foreach ($variants as $variant) {
            if (! empty($variant['id'])) {
                abort_unless(
                    $productId !== null && ProductVariant::query()
                        ->where('product_id', $productId)
                        ->whereKey((int) $variant['id'])
                        ->exists(),
                    422,
                    'Variant does not belong to this product.'
                );
            }
        }
        $request->merge([
            'variant_id' => collect($variants)->map(fn ($variant) => $variant['id'] ?? null)->all(),
            'variant_name' => collect($variants)->pluck('name')->all(),
            'variant_code' => collect($variants)->map(fn ($variant) => $variant['code'] ?? null)->all(),
            'additional_cost' => collect($variants)->map(fn ($variant) => $variant['additional_cost'] ?? 0)->all(),
            'additional_price' => collect($variants)->map(fn ($variant) => $variant['additional_price'] ?? 0)->all(),
            'variant_key' => [],
        ]);
    }

    private function categoryData(Category $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => $category->name,
            'thumbnail' => ImageHelper::publicUrl($category->image, 'category') ?? '',
            'parent_category_id' => $category->parent_id,
            'parent_category_name' => $category->parent?->name,
            'parent_category_thumbnail' => ImageHelper::publicUrl($category->parent?->image, 'category'),
            'total_products' => (int) ($category->products_count ?? $category->products()->count()),
        ];
    }

    private function brandData(Brand $brand): array
    {
        return [
            'id' => (int) $brand->id,
            'name' => $brand->name,
            'thumbnail' => ImageHelper::publicUrl($brand->image, 'brand') ?? '',
            'total_products' => (int) ($brand->products_count ?? $brand->products()->count()),
        ];
    }

    private function branchData(Branch $branch): array
    {
        return [
            'id' => (int) $branch->id,
            'name' => $branch->name,
            'email' => $branch->email,
            'phone' => $branch->phone,
            'total_purchages' => (int) ($branch->purchases_count ?? $branch->purchases()->count()),
            'address' => $branch->address,
        ];
    }

    private function productType(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return match (strtolower((string) $value)) {
            'service' => 2,
            'combo' => 3,
            default => 1,
        };
    }
}
