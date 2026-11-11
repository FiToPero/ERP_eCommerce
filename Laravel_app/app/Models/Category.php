<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::forceDeleting(function (Category $category): void {
            $ids = [$category->getKey()];
            $level = $ids;

            while ($level !== []) {
                $level = Category::withTrashed()->whereIn('parent_id', $level)->whereNotIn('id', $ids)->pluck('id')->all();
                $ids = array_merge($ids, $level);
            }

            $images = ProductImage::query()
                ->whereIn('product_id', Product::withTrashed()->whereIn('category_id', $ids)->select('id'))
                ->get();

            $category->setRelation('imagesToDelete', $images);
        });

        static::forceDeleted(function (Category $category): void {
            foreach ($category->getRelation('imagesToDelete') as $image) {
                ProductImage::deleteFileAfterCommit($image->path, $category);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories (subcategories).
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the products for this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if this is a root category (no parent).
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }
}
