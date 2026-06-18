<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ProductDetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',

        // Variantes
        'color',
        'size',
        'size_type',
        'size_system',
        'material',
        'pattern',
        'gender',
        'age_group',

        // Dimensiones físicas
        'product_length',
        'product_width',
        'product_height',
        'product_weight',
        'product_dimension_unit',
        'product_weight_unit',

        // Especificaciones / Highlights
        'product_details',
        'product_highlights',

        // Labels personalizados
        'custom_label_0',
        'custom_label_1',
        'custom_label_2',
        'custom_label_3',
        'custom_label_4',

        // Flags
        'is_adult',
        'is_bundle',
    ];

    protected $casts = [
        'product_details'    => 'array',
        'product_highlights' => 'array',
        'is_adult'           => 'boolean',
        'is_bundle'          => 'boolean',
        'product_length'     => 'decimal:2',
        'product_width'      => 'decimal:2',
        'product_height'     => 'decimal:2',
        'product_weight'     => 'decimal:2',
    ];

    /**
     * The product this detail belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stock movements for this product detail (via shared product_id).
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id');
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
}
