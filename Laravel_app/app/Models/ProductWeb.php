<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWeb extends Model
{
    /** @use HasFactory<\Database\Factories\ProductWebFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'is_active',
        'status',
        'link',
        'image_link',
        'product_type',
        'additional_image_links',
        'sale_price',
        'sale_price_start',
        'sale_price_end',
        'item_group_id',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'additional_image_links' => 'array',
        'sale_price'             => 'decimal:2',
        'sale_price_start'       => 'datetime',
        'sale_price_end'         => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
