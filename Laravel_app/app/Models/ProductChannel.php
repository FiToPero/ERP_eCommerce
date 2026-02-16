<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductChannel extends Model
{
    /** @use HasFactory<\Database\Factories\ProductChannelFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'channel_id',
        'is_active',
        'published_at',
        'custom_title',
        'custom_description',
        'custom_price',
        'metadata',
        'last_synced_at',
        'external_id',
        'external_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'custom_price' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the product that owns the product channel.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the channel that owns the product channel.
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the effective title (custom or product default).
     */
    public function getEffectiveTitleAttribute(): string
    {
        return $this->custom_title ?? $this->product->name;
    }

    /**
     * Get the effective description (custom or product default).
     */
    public function getEffectiveDescriptionAttribute(): ?string
    {
        return $this->custom_description ?? $this->product->description;
    }

    /**
     * Get the effective price (custom or product default).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->custom_price ?? $this->product->price;
    }

    /**
     * Scope to get only active product channels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get published product channels.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Check if the product channel is published.
     */
    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }

    /**
     * Mark as synced.
     */
    public function markAsSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }
}
