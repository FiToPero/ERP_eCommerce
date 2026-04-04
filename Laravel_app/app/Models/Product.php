<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'slug',
        'short_description',
        'description',
        'brand',
        'barcode',
        'mpn',
        'identifier_exists',
        'condition',
        'availability_date',
        'web_requirements',
        'google_requirements',
        'ml_requirements',
        'meta_requirements',
    ];

    protected $casts = [
        'identifier_exists'  => 'boolean',
        'availability_date'  => 'datetime',
        'web_requirements'   => 'array',
        'google_requirements' => 'array',
        'ml_requirements'    => 'array',
        'meta_requirements'  => 'array',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Stock movements for this product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get current stock based on movements.
     */
    public function getStockAttribute(): float
    {
        $total = $this->stockMovements()
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END), 0) as total")
            ->value('total');

        return (float) $total;
    }

    /**
     * Listado de Google Merchant Center para este producto.
     */
    public function productGoogle()
    {
        return $this->hasOne(ProductGoogle::class);
    }

    /**
     * Datos de publicación en Meta (Facebook/Instagram) para este producto.
     */
    public function productMeta()
    {
        return $this->hasOne(ProductMeta::class);
    }
}
