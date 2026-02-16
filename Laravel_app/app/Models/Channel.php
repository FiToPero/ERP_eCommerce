<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    /** @use HasFactory<\Database\Factories\ChannelFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get the product channels for this channel.
     */
    public function productChannels()
    {
        return $this->hasMany(ProductChannel::class);
    }

    /**
     * Get all products in this channel.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_channels')
            ->withPivot([
                'is_active',
                'published_at',
                'custom_title',
                'custom_description',
                'custom_price',
                'metadata',
                'last_synced_at',
                'external_id',
                'external_url',
            ])
            ->withTimestamps();
    }

    /**
     * Scope to get only active channels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get channel by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
