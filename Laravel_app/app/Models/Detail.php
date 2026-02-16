<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detail extends Model
{
    /** @use HasFactory<\Database\Factories\DetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'model',
        'weight',
        'weight_unit',
        'height',
        'width',
        'length',
        'dimensions_unit',
        'color',
        'material',
        'brand',
        'manufacturer',
        'origin',
        'year',
        'warranty_period',
        'warranty_details',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'year' => 'integer',
        'warranty_period' => 'integer',
    ];

    /**
     * Get the product that owns the details.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
