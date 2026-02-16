<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the details for the product.
     */
    public function detail()
    {
        return $this->hasOne(Detail::class);
    }

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('priority');
    }

    /**
     * Stock movements for this product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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

    /**
     * Get the product channels (pivot records).
     */
    public function productChannels()
    {
        return $this->hasMany(ProductChannel::class);
    }

    /**
     * Get all channels this product is in.
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'product_channels')
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
     * Get active channels for this product.
     */
    public function activeChannels()
    {
        return $this->channels()->wherePivot('is_active', true);
    }

    /**
     * Check if product is in a specific channel.
     */
    public function isInChannel(string $channelCode): bool
    {
        return $this->channels()->where('code', $channelCode)->exists();
    }

    /**
     * Get product channel by channel code.
     */
    public function getChannelData(string $channelCode): ?ProductChannel
    {
        $channel = Channel::where('code', $channelCode)->first();
        
        if (!$channel) {
            return null;
        }

        return $this->productChannels()
            ->where('channel_id', $channel->id)
            ->first();
    }
}
