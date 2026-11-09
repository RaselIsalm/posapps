<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductImei;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function branchFor(User $user): Branch
    {
        $account = Account::create([
            'name' => 'Catalog cash '.uniqid(),
            'account_number' => 'CAT-'.uniqid(),
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
        ]);

        return Branch::create([
            'name' => 'Catalog branch '.uniqid(),
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
    }

    public function test_mobile_category_and_brand_changes_are_saved_as_active_website_records(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        foreach (['create_categories', 'update_categories', 'view_categories', 'create_brands', 'update_brands', 'view_brands'] as $permissionName) {
            $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        }
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/categories/store', [
            'name' => 'Mobile category',
        ])->assertOk();
        $category = Category::query()->where('name', 'Mobile category')->firstOrFail();
        $this->assertTrue((bool) $category->status);

        $this->withToken($token)->postJson('/api/v1/categories/update/'.$category->id, [
            'name' => 'Updated mobile category',
        ])->assertOk();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated mobile category',
            'status' => 1,
        ]);

        $this->withToken($token)->postJson('/api/v1/brands/store', [
            'title' => 'Mobile brand',
        ])->assertOk();
        $brand = Brand::query()->where('name', 'Mobile brand')->firstOrFail();
        $this->assertTrue((bool) $brand->status);

        $this->withToken($token)->postJson('/api/v1/brands/update/'.$brand->id, [
            'title' => 'Updated mobile brand',
        ])->assertOk();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated mobile brand',
            'status' => 1,
        ]);
    }

    public function test_mobile_product_edit_updates_catalog_and_preserves_unedited_flags(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('update_products', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;
        $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'is_active' => true]);
        $branch = $this->branchFor($user);
        $other = User::factory()->create(['is_active' => true]);
        $otherBranch = $this->branchFor($other);
        $product = Product::create([
            'product_title' => 'Before edit',
            'product_code' => 'EDIT-'.uniqid(),
            'slug' => 'edit-'.uniqid(),
            'product_type' => 1,
            'product_cost' => 5,
            'selling_price' => 10,
            'wholesale_price' => 8,
            'product_unit' => $unit->id,
            'purchase_unit' => $unit->id,
            'selling_unit' => $unit->id,
            'tax_method' => 'inclusive',
            'tax' => 5,
            'has_expiry_date' => true,
            'is_feature' => true,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $branch->id, 'product_id' => $product->id,
            'qty' => 3, 'sell_price' => 12,
        ]);
        ProductBranch::create([
            'branch_id' => $otherBranch->id, 'product_id' => $product->id,
            'qty' => 70, 'sell_price' => 12,
        ]);

        $this->withToken($token)->putJson('/api/v1/product/update/'.$product->id, [
            'name' => 'After edit',
            'code' => $product->product_code,
            'type' => 'Standard',
            'cost' => 6,
            'price' => 12,
            'unit_id' => $unit->id,
            'purchase_unit' => $unit->id,
            'selling_unit' => $unit->id,
            'alert_quantity' => 4,
            'tax' => 5,
            'tax_method' => 'inclusive',
            'is_active' => true,
            'has_expiry_date' => true,
            'is_feature' => true,
        ])->assertOk()
            ->assertJsonPath('data.product.name', 'After edit')
            ->assertJsonPath('data.product.stock', 3);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'product_title' => 'After edit',
            'product_cost' => 6,
            'selling_price' => 12,
            'product_unit' => $unit->id,
            'purchase_unit' => $unit->id,
            'selling_unit' => $unit->id,
            'tax_method' => 'inclusive',
            'has_expiry_date' => 1,
            'is_feature' => 1,
        ]);
    }

    public function test_mobile_product_search_exposes_branch_variant_stock_and_available_imeis(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        foreach (['view_products', 'view_quick_sale', 'create_quick_sale'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $token = $user->createToken('mobile')->plainTextToken;
        $branch = $this->branchFor($user);
        $product = Product::create([
            'product_title' => 'Variant phone', 'product_code' => 'PHONE-'.uniqid(),
            'slug' => 'variant-phone-'.uniqid(), 'product_type' => 1,
            'product_cost' => 100, 'selling_price' => 150, 'is_active' => true,
            'is_variation' => true, 'has_imei' => true, 'created_by' => $user->id,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'variant_name' => '128 GB',
            'variant_code' => 'PHONE-128', 'additional_cost' => 20, 'additional_price' => 30,
        ]);
        ProductBranch::create([
            'branch_id' => $branch->id, 'product_id' => $product->id,
            'variant_id' => $variant->id, 'qty' => 2, 'sell_price' => 190,
        ]);
        ProductImei::create([
            'product_id' => $product->id, 'variant_id' => $variant->id,
            'branch_id' => $branch->id, 'imei_number' => 'IMEI-LOCAL-01',
            'status' => 'in_stock', 'is_sold' => false, 'created_by' => $user->id,
        ]);

        $this->withToken($token)->getJson('/api/v1/product/search?branch_id='.$branch->id)
            ->assertOk()
            ->assertJsonPath('data.products.0.has_imei', true)
            ->assertJsonPath('data.products.0.variants.0.id', $variant->id)
            ->assertJsonPath('data.products.0.variants.0.stock', 2)
            ->assertJsonPath('data.products.0.variants.0.price', 190);

        $this->withToken($token)->getJson('/api/v1/product/'.$product->id.'/imeis?branch_id='.$branch->id.'&variant_id='.$variant->id)
            ->assertOk()
            ->assertJsonPath('data.imeis.0.imei_number', 'IMEI-LOCAL-01');

        $other = User::factory()->create(['is_active' => true]);
        $otherBranch = $this->branchFor($other);
        $this->withToken($token)->getJson('/api/v1/product/'.$product->id.'/imeis?branch_id='.$otherBranch->id.'&variant_id='.$variant->id)
            ->assertForbidden();
    }

    public function test_mobile_edit_updates_existing_variant_and_imei_tracking(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('update_products', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;
        $unit = Unit::create(['name' => 'Variant piece', 'short_code' => 'vp', 'is_active' => true]);
        $product = Product::create([
            'product_title' => 'Editable phone', 'product_code' => 'EDIT-VAR-'.uniqid(),
            'slug' => 'editable-phone-'.uniqid(), 'product_type' => 1,
            'product_cost' => 50, 'selling_price' => 80, 'product_unit' => $unit->id,
            'purchase_unit' => $unit->id, 'selling_unit' => $unit->id,
            'is_variation' => true, 'has_imei' => true, 'is_active' => true,
            'created_by' => $user->id,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'variant_name' => 'Old size',
            'variant_code' => 'OLD-SIZE', 'additional_cost' => 1, 'additional_price' => 2,
        ]);

        $this->withToken($token)->putJson('/api/v1/product/update/'.$product->id, [
            'name' => 'Editable phone updated', 'code' => $product->product_code,
            'type' => 'Standard', 'cost' => 55, 'price' => 90,
            'unit_id' => $unit->id, 'purchase_unit' => $unit->id,
            'selling_unit' => $unit->id, 'alert_quantity' => 0, 'tax' => 0,
            'is_active' => true, 'is_variation' => true, 'has_imei' => true,
            'variants' => [[
                'id' => $variant->id, 'name' => 'New size', 'code' => 'NEW-SIZE',
                'additional_cost' => 3, 'additional_price' => 8,
            ]],
        ])->assertOk()->assertJsonPath('data.product.variants.0.name', 'New size');

        $this->assertDatabaseHas('products', [
            'id' => $product->id, 'product_title' => 'Editable phone updated',
            'is_variation' => 1, 'has_imei' => 1,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id, 'variant_name' => 'New size',
            'variant_code' => 'NEW-SIZE', 'additional_price' => 8,
        ]);
    }

    public function test_mobile_barcode_generation_respects_branch_access_and_label_limit(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('view_print_barcode', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;
        $ownBranch = $this->branchFor($user);
        $other = User::factory()->create(['is_active' => true]);
        $otherBranch = $this->branchFor($other);
        $ownProduct = Product::create([
            'product_title' => 'Barcode item', 'product_code' => 'BAR-'.uniqid(),
            'slug' => 'barcode-'.uniqid(), 'product_type' => 1, 'product_cost' => 5,
            'selling_price' => 12, 'quantity' => 2, 'is_active' => true, 'created_by' => $user->id,
        ]);
        ProductBranch::create(['branch_id' => $ownBranch->id, 'product_id' => $ownProduct->id, 'qty' => 2, 'sell_price' => 12]);
        $otherProduct = Product::create([
            'product_title' => 'Private barcode item', 'product_code' => 'BAR-'.uniqid(),
            'slug' => 'barcode-'.uniqid(), 'product_type' => 1, 'product_cost' => 5,
            'selling_price' => 12, 'quantity' => 1, 'is_active' => true, 'created_by' => $other->id,
        ]);
        ProductBranch::create(['branch_id' => $otherBranch->id, 'product_id' => $otherProduct->id, 'qty' => 1, 'sell_price' => 12]);

        $this->withToken($token)->postJson('/api/v1/products/barcodes', [
            'items' => [['product_id' => $ownProduct->id, 'quantity' => 2]],
        ])->assertOk()
            ->assertJsonPath('data.labels.0.code', $ownProduct->product_code)
            ->assertJsonPath('data.labels.0.quantity', 2);

        $this->withToken($token)->postJson('/api/v1/products/barcodes', [
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 1]],
        ])->assertNotFound();

        $this->withToken($token)->postJson('/api/v1/products/barcodes', [
            'items' => [
                ['product_id' => $ownProduct->id, 'quantity' => 100],
                ['product_id' => $ownProduct->id, 'quantity' => 100],
                ['product_id' => $ownProduct->id, 'quantity' => 100],
                ['product_id' => $ownProduct->id, 'quantity' => 100],
                ['product_id' => $ownProduct->id, 'quantity' => 100],
                ['product_id' => $ownProduct->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();
    }

    public function test_mobile_can_download_template_and_import_products_into_an_accessible_branch(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('create_products', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;
        $branch = $this->branchFor($user);
        Category::create(['name' => 'Import category', 'slug' => 'import-category', 'status' => true]);
        Brand::create(['name' => 'Import brand', 'slug' => 'import-brand', 'status' => true]);
        Unit::create(['name' => 'Import piece', 'short_code' => 'ipc', 'is_active' => true]);

        $sample = $this->withToken($token)->get('/api/v1/products/import/sample')->assertOk();
        $this->assertStringStartsWith('PK', $sample->streamedContent());
        $this->assertStringContainsString('product_import_sample.xlsx', $sample->headers->get('content-disposition'));

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            \App\Services\ProductImportService::HEADERS,
            ['Imported mobile product', 'MOB-IMPORT-001', 'physical', '4', '9', 'Import category', 'Import brand', 'Import piece', '', '7', '0', 'No Tax', 'Local import test', '1'],
            ['Broken row', 'MOB-IMPORT-002', 'not-a-type', '4', '9', 'Import category', 'Import brand', 'Import piece', '', '1', '0', 'No Tax', '', '1'],
        ]);
        $tempPath = tempnam(sys_get_temp_dir(), 'onepos-product-import-');
        (new Xlsx($spreadsheet))->save($tempPath);
        $upload = new UploadedFile(
            $tempPath,
            'mobile-products.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        try {
            $response = $this->withToken($token)->post('/api/v1/products/import', [
                'branch_id' => $branch->id,
                'excel_file' => $upload,
            ])->assertOk()->assertJsonPath('data.imported', 1)->assertJsonPath('data.failed', 1);
        } finally {
            @unlink($tempPath);
        }

        $product = Product::where('product_code', 'MOB-IMPORT-001')->firstOrFail();
        $this->assertSame('no_tax', $product->tax_method);
        $this->assertDatabaseHas('product_branches', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'qty' => 7,
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'qty' => 7,
            'type' => 'import',
        ]);
        $this->assertSame(1, $response->json('data.failed'));
        $this->assertStringContainsString('Invalid product_type', $response->json('data.errors.0.message'));
    }
}
