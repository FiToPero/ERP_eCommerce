<?php

namespace App\Models;

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

    /**
     * Product of this movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
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
