<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMercadolibre extends Model
{
    /** @use HasFactory<\Database\Factories\ProductMercadolibreFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'is_active',
        'status',
        'ml_id',
        'ml_url',
        'last_synced_at',
        'sync_errors',
        // Required
        'title',
        'category_id',
        'price',
        'currency_id',
        'available_quantity',
        'buying_mode',
        'listing_type_id',
        'condition',
        'pictures',
        // Conditional
        'brand',
        'gtin',
        'attributes',
        // Recommended
        'sale_terms',
        'shipping',
        'video_id',
        'warranty',
        'variations',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'price'           => 'decimal:2',
        'available_quantity' => 'integer',
        'last_synced_at'  => 'datetime',
        'sync_errors'     => 'array',
        'pictures'        => 'array',
        'attributes'      => 'array',
        'sale_terms'      => 'array',
        'shipping'        => 'array',
        'variations'      => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
