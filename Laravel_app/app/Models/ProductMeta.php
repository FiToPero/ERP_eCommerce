<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMeta extends Model
{
    /** @use HasFactory<\Database\Factories\ProductMetaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Estado sincronización
        'product_id',
        'is_active',
        'status',
        'meta_id',
        'permalink',
        'last_synced_at',
        'sync_errors',

        // Required
        'title',
        'description',
        'availability',
        'condition',
        'price',
        'link',

        // Recommended
        'item_group_id',
        'color',
        'size',
        'google_product_category',
        'product_type',
        'sale_price',
        'sale_price_start',
        'sale_price_end',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'sync_errors'            => 'array',
        'sale_price'             => 'decimal:2',
        'last_synced_at'         => 'datetime',
        'sale_price_start'       => 'datetime',
        'sale_price_end'         => 'datetime',
    ];

    // Relationships

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
