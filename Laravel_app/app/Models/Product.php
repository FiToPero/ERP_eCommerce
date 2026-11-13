<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'image_directory',
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
        'web_requirements',
        'google_requirements',
        'ml_requirements',
        'meta_requirements',
    ];

    protected $casts = [
        'identifier_exists'  => 'boolean',
        'availability_date'  => 'datetime',
        'web_requirements'   => 'array',
        'google_requirements' => 'array',
        'ml_requirements'    => 'array',
        'meta_requirements'  => 'array',
    ];

    protected $appends = ['image_link', 'additional_image_links', 'video_link'];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->image_directory)) {
                $product->image_directory = self::makeImageDirectory($product->name);
            }
        });

        static::forceDeleting(function (Product $product): void {
            $product->setRelation('imagesToDelete', $product->images()->get());
            $product->setRelation('videosToDelete', $product->videos()->get());
        });

        static::forceDeleted(function (Product $product): void {
            foreach ($product->getRelation('imagesToDelete') as $image) {
                ProductImage::deleteFileAfterCommit($image->path, $product);
            }
            foreach ($product->getRelation('videosToDelete') as $video) {
                ProductVideo::deleteFilesAfterCommit($video->path, $product);
            }
        });
    }

    public static function makeImageDirectory(string $name): string
    {
        $slug = trim(substr(Str::slug($name), 0, 50), '-') ?: 'producto';

        return $slug.'-'.strtolower((string) Str::ulid());
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position')->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('position')->orderBy('id');
    }

    public function getVideoLinkAttribute(): ?string
    {
        return $this->videos->first()?->url;
    }

    public function getImageLinkAttribute(): ?string
    {
        return $this->images->first()?->url;
    }

    public function getAdditionalImageLinksAttribute(): array
    {
        return $this->images->skip(1)->map(fn (ProductImage $image): string => $image->url)->values()->all();
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
    public function productWebEcommerce()
    {
        return $this->hasOne(ProductWebEcommerce::class);
    }

    /**
     * Datos de publicación en el sitio web de venta para este producto.
     */
    public function productWebVenta()
    {
        return $this->hasOne(ProductWebVenta::class);
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
