<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTables;
use App\Helpers\ImageHelper;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductComboItem;
use App\Models\ProductMedia;
use App\Models\ProductSale;
use App\Models\ProductSeo;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\PurchaseDetail;
use App\Models\Unit;
use App\Models\VariantOption;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(ProductDataTables $dataTable)
    {
        return $dataTable->render('backend.product.index');
    }



    public function searchForCombo(Request $request)
    {
        $search = $request->get('q');

        // Exclude combo products themselves from being added to another combo to avoid nesting complexity
        $query = Product::with('productVariants', 'unit')
            ->active()
            ->whereIn('product_type', [1, 2, 4]); // 1=Physical, 2=Digital, 4=Service

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_title', 'LIKE', "%{$search}%")
                    ->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->limit(20)->get();

        $results = [];
        foreach ($products as $product) {
            if ($product->is_variation && $product->productVariants->count() > 0) {
                foreach ($product->productVariants as $variant) {
                    $results[] = [
                        'id' => $product->id.'-'.$variant->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'text' => $product->product_title.' - '.$variant->item_code,
                        'price' => $variant->sellingPriceFromBase($product->selling_price),
                        'cost' => $variant->costFromBase($product->product_cost),
                        'code' => $variant->item_code,
                    ];
                }
            } else {
                $results[] = [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'text' => $product->product_title,
                    'price' => $product->selling_price,
                    'cost' => $product->product_cost,
                    'code' => $product->product_code,
                ];
            }
        }

        return response()->json($results);
    }

    public function create()
    {
        $categories = Category::active()->get(['id', 'name']);
        $brands = Brand::active()->get(['id', 'name']);
        $units = Unit::active()->get(['id', 'name']);

        return view('backend.product.create', compact('categories', 'brands', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|unique:products,product_code',
            'product_title' => 'required',
            'product_type' => 'required',
            'product_cost' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'wholesale_price' => 'nullable|numeric',
            'max_comission' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'promotional_price' => 'nullable|numeric',
            'limit' => 'nullable|integer',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'variant_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ], [
            'max_comission.numeric' => 'The max commission must be a valid number.',
            'tax.numeric' => 'The tax must be a valid number.',
            'wholesale_price.numeric' => 'The wholesale price must be a valid number.',
            'promotional_price.numeric' => 'The promotional price must be a valid number.',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::create([
                'product_title' => $request->product_title,
                'product_code' => $request->product_code,
                'slug' => Str::slug($request->product_title.'-'.uniqid()),
                'has_imei' => $request->boolean('has_imei') ? 1 : 0,
                'product_type' => $request->product_type,
                'product_cost' => $request->product_cost,
                'selling_price' => $request->selling_price,
                'wholesale_price' => $request->filled('wholesale_price') ? $request->wholesale_price : null,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'comission_type' => $request->comission_type,
                'max_comission' => $request->max_comission,
                'tax_method' => $request->tax_method,
                'tax' => $request->tax,
                'product_unit' => $request->product_unit,
                'purchase_unit' => $request->purchase_unit,
                'selling_unit' => $request->selling_unit,
                'description' => $request->description,
                'is_variation' => $request->boolean('is_variation') ? 1 : 0,
                'has_expiry_date' => $request->boolean('has_expiry_date') ? 1 : 0,
                'is_feature' => $request->boolean('is_feature') ? 1 : 0,
                'is_active' => $request->boolean('is_active') ? 1 : 0,
                'promotional_price' => $request->promotional_price,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'limit' => $request->limit ?? 0,
                'is_online_seo' => $request->boolean('is_online') ? 1 : 0,
                'thumbnail' => $request->hasFile('thumbnail')
                    ? ImageHelper::uploadImage(
                        time().'_'.uniqid().'.'.$request->file('thumbnail')->getClientOriginalExtension(),
                        $request->file('thumbnail'),
                        'products'
                    )
                    : null,
                'tags' => is_array($request->tags) ? implode(',', $request->tags) : null,
                'created_by' => Auth::user()->id,
            ]);

            // Product SEO
            if ($product->is_online_seo) {
                ProductSeo::create([
                    'product_id' => $product->id,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'meta_keywords' => $request->meta_keyword,
                    'og_title' => $request->og_title,
                    'og_description' => $request->og_description,
                    'og_url' => $request->og_url,
                    'og_locale' => $request->og_locale,
                    'og_type' => $request->og_type,
                    'og_image' => $request->hasFile('og_image')
                        ? ImageHelper::uploadImage(
                            $request->og_title.'-'.time().'.'.$request->file('og_image')->getClientOriginalExtension(),
                            $request->file('og_image'),
                            'products/og_images',
                        ) : null,
                ]);
            }

            //  Product Images
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    ProductMedia::create([
                        'product_id' => $product->id,
                        'image' => ImageHelper::uploadImage(
                            'product_'.time().'_'.uniqid().'.'.$image->getClientOriginalExtension(),
                            $image,
                            'products/gallery'
                        ),
                        'status' => 1,
                    ]);
                }
            }
            // Product Variants
            if ($product->is_variation) {
                // Loop through variant_names
                foreach ($request->variant_name as $index => $name) {
                    // --- Image Upload Logic ---
                    $variantImage = null;
                    if ($request->hasFile('variant_image') && isset($request->file('variant_image')[$index])) {
                        $imageFile = $request->file('variant_image')[$index];

                        $variantImage = ImageHelper::uploadImage(
                            'variant_'.time().'_'.uniqid().'.'.$imageFile->getClientOriginalExtension(),
                            $imageFile,
                            'products/variants'
                        );
                    }
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_name' => $name,
                        'variant_code' => $request->variant_code[$index],
                        'additional_cost' => $request->additional_cost[$index] ?? 0,
                        'additional_price' => $request->additional_price[$index] ?? 0,
                        'image' => $variantImage ?? null,
                    ]);

                    // Map options and values
                    foreach ($request->variant_key as $keyIndex => $optionKey) {
                        $variantNameParts = explode('-', $name);
                        $value = $variantNameParts[$keyIndex] ?? null;

                        $option = VariantOption::firstOrCreate([
                            'product_id' => $product->id,
                            'name' => $optionKey,
                        ]);

                        $val = VariantValue::firstOrCreate([
                            'variant_option_id' => $option->id,
                            'value' => $value,
                        ]);

                        ProductVariantValue::create([
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'variant_option_id' => $option->id,
                            'variant_value_id' => $val->id,
                        ]);
                    }
                }
            }

            // Product Combo Items
            if ($product->product_type == 3 && $request->has('combo_product_ids')) {
                foreach ($request->combo_product_ids as $index => $comboProductId) {
                    $qty = $request->combo_quantities[$index] ?? 1;
                    $unitPrice = $request->combo_unit_prices[$index] ?? 0;
                    ProductComboItem::create([
                        'product_id' => $product->id,
                        'combo_item_id' => $comboProductId,
                        'variant_id' => ! empty($request->combo_variant_ids[$index]) ? $request->combo_variant_ids[$index] : null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $qty * $unitPrice,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to save product: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::with([
            'seo',
            'media',
            'productVariants',
            'comboItems.comboItem',
            'comboItems.variant',
            'options' => function ($q) {
                $q->with('values');
            },
        ])->findOrFail($id);

        // IDOR Check: Ensure user is Admin/Super-Admin or the creator of the product
        if (! auth()->user()->hasRole(['Super-Admin', 'Admin']) && $product->created_by != auth()->id()) {
            return redirect()->route('products.index')->with('error', 'Unauthorized Access: You can only edit your own products.');
        }
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);
        $units = Unit::where('is_active', 1)->get(['id', 'name']);

        return view('backend.product.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function uploadQuillImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $name = time().'-'.Str::random(8).'.'.$image->getClientOriginalExtension();

            ImageHelper::uploadImage($name, $image, 'quill');

            $url = asset("storage/quill/{$name}");

            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_title' => 'required|string|max:255',
            'product_code' => 'required|unique:products,product_code,'.$id,
            'product_type' => 'required',
            'product_cost' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'wholesale_price' => 'nullable|numeric',
            'max_comission' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'promotional_price' => 'nullable|numeric',
            'limit' => 'nullable|integer',
            'category_id' => 'nullable',
            'brand_id' => 'nullable',
            'product_unit' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'variant_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ], [
            'max_comission.numeric' => 'The max commission must be a valid number.',
            'tax.numeric' => 'The tax must be a valid number.',
            'wholesale_price.numeric' => 'The wholesale price must be a valid number.',
            'promotional_price.numeric' => 'The promotional price must be a valid number.',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // IDOR Check: Ensure user is Admin/Super-Admin or the creator of the product
            if (! auth()->user()->hasRole(['Super-Admin', 'Admin']) && $product->created_by != auth()->id()) {
                return redirect()->route('products.index')->with('error', 'Unauthorized Access: You can only update your own products.');
            }

            $data = $request->only([
                'product_title',
                'product_code',
                'product_type',
                'product_cost',
                'selling_price',
                'wholesale_price',
                'category_id',
                'brand_id',
                'comission_type',
                'max_comission',
                'tax_method',
                'tax',
                'product_unit',
                'purchase_unit',
                'selling_unit',
                'description',
                'promotional_price',
                'start_date',
                'end_date',
                'limit',
            ]);

            $data['is_variation'] = $request->boolean('is_variation') ? 1 : 0;
            $data['has_imei'] = $request->boolean('has_imei') ? 1 : 0;
            $data['has_expiry_date'] = $request->boolean('has_expiry_date') ? 1 : 0;
            $data['is_feature'] = $request->boolean('is_feature') ? 1 : 0;
            $data['is_active'] = $request->boolean('is_active') ? 1 : 0;
            $data['is_online_seo'] = $request->boolean('is_online') ? 1 : 0;
            $data['wholesale_price'] = $request->filled('wholesale_price') ? $request->wholesale_price : null;
            $data['tags'] = is_array($request->tags) ? implode(',', $request->tags) : null;
            $data['updated_by'] = Auth::id();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = ImageHelper::updateImage(
                    time().'_'.uniqid().'.'.$request->file('thumbnail')->getClientOriginalExtension(),
                    $request->file('thumbnail'),
                    'products',
                    $product->thumbnail
                );
            } elseif ($request->input('remove_thumbnail') === '1') {
                ImageHelper::deleteImage($product->thumbnail, 'products');
                $data['thumbnail'] = null;
            }

            $product->update($data);

            if ($data['is_online_seo']) {
                $ogImage = $product->seo ? $product->seo->og_image : null;

                if ($request->hasFile('og_image')) {
                    $ogImage = ImageHelper::updateImage(
                        $request->og_title.'-'.time().'.'.$request->file('og_image')->getClientOriginalExtension(),
                        $request->file('og_image'),
                        'products/og_images',
                        $ogImage
                    );
                }

                ProductSeo::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'meta_title' => $request->meta_title,
                        'meta_description' => $request->meta_description,
                        'meta_keywords' => $request->meta_keyword,
                        'og_title' => $request->og_title,
                        'og_description' => $request->og_description,
                        'og_url' => $request->og_url,
                        'og_locale' => $request->og_locale,
                        'og_type' => $request->og_type,
                        'og_image' => $ogImage,
                    ]
                );
            } else {
                if ($product->seo) {
                    ImageHelper::deleteImage($product->seo->og_image, 'products/og_images');
                    $product->seo()->delete();
                }
            }

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    ProductMedia::create([
                        'product_id' => $product->id,
                        'image' => ImageHelper::uploadImage('gallery_'.time().uniqid().'.'.$image->getClientOriginalExtension(), $image, 'products/gallery'),
                        'status' => 1,
                    ]);
                }
            }

            if ($data['is_variation']) {
                $existingVariantIds = [];

                if ($request->variant_name && is_array($request->variant_name)) {
                    foreach ($request->variant_name as $index => $name) {
                        $attributes = [
                            'variant_name' => $name,
                            'variant_code' => $request->variant_code[$index] ?? null,
                            'additional_cost' => $request->additional_cost[$index] ?? 0,
                            'additional_price' => $request->additional_price[$index] ?? 0,
                        ];
                        $variantId = $request->input("variant_id.{$index}");
                        if ($variantId) {
                            $variant = $product->productVariants()->findOrFail($variantId);
                            $variant->update($attributes);
                        } else {
                            $variant = ProductVariant::updateOrCreate(
                                ['product_id' => $product->id, 'variant_name' => $name],
                                $attributes
                            );
                        }

                        if ($request->hasFile('variant_image') && isset($request->file('variant_image')[$index])) {
                            $imageFile = $request->file('variant_image')[$index];
                            $variant->image = ImageHelper::updateImage(
                                'variant_'.time().'_'.uniqid().'.'.$imageFile->getClientOriginalExtension(),
                                $imageFile,
                                'products/variants',
                                $variant->image
                            );
                            $variant->save();
                        }

                        $existingVariantIds[] = $variant->id;

                        // Clear old option mappings for this variant before recreating them
                        $variant->values()->delete();

                        if (isset($request->variant_key)) {
                            foreach ($request->variant_key as $keyIndex => $optionKey) {
                                $variantNameParts = explode('-', $name);
                                $value = $variantNameParts[$keyIndex] ?? null;

                                if ($optionKey && $value) {
                                    $option = VariantOption::firstOrCreate([
                                        'product_id' => $product->id,
                                        'name' => $optionKey,
                                    ]);

                                    $val = VariantValue::firstOrCreate([
                                        'variant_option_id' => $option->id,
                                        'value' => $value,
                                    ]);

                                    ProductVariantValue::firstOrCreate([
                                        'product_id' => $product->id,
                                        'product_variant_id' => $variant->id,
                                        'variant_option_id' => $option->id,
                                        'variant_value_id' => $val->id,
                                    ]);
                                }
                            }
                        }
                    }
                }
                
                // Cleanup removed variants safely (if they are not tied to sales, they can be deleted)
                $variantsToRemove = $product->productVariants()->whereNotIn('id', $existingVariantIds)->get();
                foreach($variantsToRemove as $v) {
                    $hasSales = ProductSale::where('variant_id', $v->id)->exists();
                    $hasPurchases = PurchaseDetail::where('variant_id', $v->id)->exists();
                    $inCombo = ProductComboItem::where('variant_id', $v->id)->exists();
                    $hasInventoryHistory = DB::table('product_branches')->where('variant_id', $v->id)->exists()
                        || DB::table('product_imeis')->where('variant_id', $v->id)->exists();
                    if(!$hasSales && !$hasPurchases && !$inCombo && !$hasInventoryHistory) {
                        ImageHelper::deleteImage($v->image, 'products/variants');
                        $v->delete();
                    }
                }
            } else {
                // Safely delete all variants if is_variation is toggled off
                $variants = $product->productVariants()->get();
                $allSafelyDeleted = true;
                foreach($variants as $v) {
                    $hasSales = ProductSale::where('variant_id', $v->id)->exists();
                    $hasPurchases = PurchaseDetail::where('variant_id', $v->id)->exists();
                    $inCombo = ProductComboItem::where('variant_id', $v->id)->exists();
                    $hasInventoryHistory = DB::table('product_branches')->where('variant_id', $v->id)->exists()
                        || DB::table('product_imeis')->where('variant_id', $v->id)->exists();
                    if(!$hasSales && !$hasPurchases && !$inCombo && !$hasInventoryHistory) {
                        ImageHelper::deleteImage($v->image, 'products/variants');
                        $v->delete();
                    } else {
                        $allSafelyDeleted = false;
                    }
                }
                
                // If all variants were deleted safely, we can also clean up the options
                if ($allSafelyDeleted) {
                    $product->options()->delete();
                }
            }

            // Sync Product Combo Items
            if ($product->product_type == 3 && $request->has('combo_product_ids')) {
                $existingComboIds = [];
                foreach ($request->combo_product_ids as $index => $comboProductId) {
                    $qty = $request->combo_quantities[$index] ?? 1;
                    $unitPrice = $request->combo_unit_prices[$index] ?? 0;
                    $variantId = ! empty($request->combo_variant_ids[$index]) ? $request->combo_variant_ids[$index] : null;

                    $comboItem = ProductComboItem::updateOrCreate([
                        'product_id' => $product->id,
                        'combo_item_id' => $comboProductId,
                        'variant_id' => $variantId,
                    ], [
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $qty * $unitPrice,
                    ]);
                    $existingComboIds[] = $comboItem->id;
                }
                $product->comboItems()->whereNotIn('id', $existingComboIds)->delete();
            } else {
                $product->comboItems()->delete();
            }

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Error: '.$e->getMessage());

            return back()->with('error', 'Failed to update: '.$e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'brand',
            'unit',
            'productBranches.branch',
            'productVariants',
            'imeis',
        ])->findOrFail($id);

        return view('backend.product.details', compact('product'))->render();
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // IDOR Check: Ensure user is Admin/Super-Admin or the creator of the product
            if (! auth()->user()->hasRole(['Super-Admin', 'Admin']) && $product->created_by != auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized Access: You can only delete your own products.',
                ], 403);
            }
            $hasSales = ProductSale::where('product_id', $id)->exists();
            $hasPurchases = PurchaseDetail::where('product_id', $id)->exists();
            $inCombo = ProductComboItem::where('combo_item_id', $id)->exists();

            if ($hasSales || $hasPurchases || $inCombo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warning: This product cannot be deleted because it is associated with sales, purchases, or is part of a Combo Product!',
                ], 403);
            }

            ImageHelper::deleteImage($product->thumbnail, 'products');
            if ($product->seo) {
                ImageHelper::deleteImage($product->seo->og_image, 'products/og_images');
            }
            foreach ($product->media as $media) {
                ImageHelper::deleteImage($media->image, 'products/gallery');
            }
            foreach ($product->productVariants as $variant) {
                ImageHelper::deleteImage($variant->image, 'products/variants');
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            if (empty($ids) || ! is_array($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products selected.',
                ], 400);
            }

            // Optional: IDOR check for bulk delete if strict mode is needed
            // For now, assuming permission middleware covers basic access

            $products = Product::whereIn('id', $ids)->get();

            $deletedCount = 0;
            $failedCount = 0;

            foreach ($products as $product) {
                // Check if product has sales or purchases or in combo
                $hasSales = ProductSale::where('product_id', $product->id)->exists();
                $hasPurchases = PurchaseDetail::where('product_id', $product->id)->exists();
                $inCombo = ProductComboItem::where('combo_item_id', $product->id)->exists();

                if ($hasSales || $hasPurchases || $inCombo) {
                    $failedCount++;
                    continue; // Skip deleting this product
                }

                ImageHelper::deleteImage($product->thumbnail, 'products');
                if ($product->seo) {
                    ImageHelper::deleteImage($product->seo->og_image, 'products/og_images');
                }
                foreach ($product->media as $media) {
                    ImageHelper::deleteImage($media->image, 'products/gallery');
                }
                foreach ($product->productVariants as $variant) {
                    ImageHelper::deleteImage($variant->image, 'products/variants');
                }

                $product->delete();
                $deletedCount++;
            }

            if ($deletedCount > 0 && $failedCount == 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deletedCount} product(s).",
                ]);
            } elseif ($deletedCount > 0 && $failedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Deleted {$deletedCount} product(s). {$failedCount} product(s) could not be deleted due to existing sales/purchases/combos.",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "No products could be deleted because they are associated with existing sales, purchases, or combos.",
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteMedia($id)
    {
        try {
            $media = ProductMedia::findOrFail($id);
            // Optionally delete the physical file
            $path = storage_path('app/public/products/gallery/' . $media->image);
            if(file_exists($path)) {
                unlink($path);
            }
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image.'
            ], 500);
        }
    }
}
