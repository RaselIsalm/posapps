<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseModel
{
    protected $fillable = [
        'product_title',
        'product_code',
        'slug',
        'has_imei',
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
        'quantity',
        'alert_quantity',
        'description',
        'is_variation',
        'has_expiry_date',
        'is_feature',
        'promotional_price',
        'start_date',
        'end_date',
        'limit',
        'is_online_seo',
        'thumbnail',
        'is_published',
        'is_active',
        'tags',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function seo()
    {
        return $this->hasOne(ProductSeo::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'product_unit');
    }

    public function sellingUnit()
    {
        return $this->belongsTo(Unit::class, 'selling_unit');
    }

    public function options()
    {
        return $this->hasMany(VariantOption::class);
    }

    public function variantOptions()
    {
        return $this->hasManyThrough(
            VariantOption::class,
            ProductVariantValue::class,
            'product_id',
            'id',
            'id',
            'variant_option_id'
        )->distinct();
    }

    public function variantValues()
    {
        return $this->hasManyThrough(
            VariantValue::class,
            ProductVariantValue::class,
            'product_id',
            'id',
            'id',
            'variant_value_id'
        )->distinct();
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function productBranches()
    {
        return $this->hasMany(ProductBranch::class, 'product_id');
    }

    public function imeis()
    {
        return $this->hasMany(ProductImei::class);
    }

    public function comboItems(): HasMany
    {
        return $this->hasMany(ProductComboItem::class, 'product_id');
    }

    public function comboItemProducts()
    {
        return $this->belongsToMany(Product::class, 'product_combo_items', 'product_id', 'combo_item_id')
            ->withPivot('quantity', 'unit_price', 'total_price', 'variant_id');
    }
}
