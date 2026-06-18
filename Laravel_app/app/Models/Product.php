<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'slug',
        'short_description',
        'description',
        'brand',
        'barcode',
        'mpn',
        'identifier_exists',
        'condition',
        'availability_date',
        'image_link',
        'additional_image_links',
        'video_link',
        'web_requirements',
        'google_requirements',
        'ml_requirements',
        'meta_requirements',
    ];

    protected $casts = [
        'identifier_exists'  => 'boolean',
        'availability_date'  => 'datetime',
        'additional_image_links' => 'array',
        'web_requirements'   => 'array',
        'google_requirements' => 'array',
        'ml_requirements'    => 'array',
        'meta_requirements'  => 'array',
    ];

    public static function resolveImageUrl(?string $imageLink, string $disk = 'public'): ?string
    {
        if (blank($imageLink)) {
            return null;
        }

        if (filter_var($imageLink, FILTER_VALIDATE_URL) !== false || str($imageLink)->startsWith('data:')) {
            return $imageLink;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        return $storage->url($imageLink);
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Stock movements for this product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
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
     * Listado de Google Merchant Center para este producto.
     */
    public function productGoogle()
    {
        return $this->hasOne(ProductGoogle::class);
    }

    /**
     * Datos de publicación en el sitio web para este producto.
     */
    public function productWeb()
    {
        return $this->hasOne(ProductWeb::class);
    }

    /**
     * Datos de publicación en MercadoLibre para este producto.
     */
    public function productMercadolibre()
    {
        return $this->hasOne(ProductMercadolibre::class);
    }

    /**
     * Datos de publicación en Meta (Facebook/Instagram) para este producto.
     */
    public function productMeta()
    {
        return $this->hasOne(ProductMeta::class);
    }

    /**
     * Información extendida del producto.
     */
    public function productDetail()
    {
        return $this->hasOne(ProductDetail::class);
    }
}
