<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductGoogle extends Model
{
    /** @use HasFactory<\Database\Factories\ProductGoogleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',

        // Estado de sincronización
        'is_active',
        'status',
        'google_id',
        'google_url',
        'last_synced_at',
        'sync_errors',

        // Required
        'title',
        'description',
        'link',
        'price',
        'currency',
        'availability',

        // Recommended
        'google_product_category',
        'product_type',
        'sale_price',
        'sale_price_start',
        'sale_price_end',
        'item_group_id',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'last_synced_at'         => 'datetime',
        'sync_errors'            => 'array',
        'price'                  => 'decimal:2',
        'sale_price'             => 'decimal:2',
        'sale_price_start'       => 'datetime',
        'sale_price_end'         => 'datetime',
    ];

    /**
     * The product this record belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: only active (enabled for Google sync).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by sync status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
