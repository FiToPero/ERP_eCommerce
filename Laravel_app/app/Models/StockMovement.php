<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'storage_id',
        'direction',
        'type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'moved_at',
        'note',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'moved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public static function directionOptions(): array
    {
        return [
            'in'  => 'In',
            'out' => 'Out',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'purchase'     => 'Purchase',
            'sale'         => 'Sale',
            'adjust'       => 'Adjustment',
            'transfer_in'  => 'Transfer In',
            'transfer_out' => 'Transfer Out',
        ];
    }

    /**
     * Product of this movement.
     */
    public function productDetail()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Backward-compatible alias for the related product.
     */
    public function productDetails()
    {
        return $this->productDetail();
    }

    /**
     * Backward-compatible alias for the related product.
     */
    public function product()
    {
        return $this->productDetail();
    }

    /**
     * Storage of this movement.
     */
    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'in');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'out');
    }
}
