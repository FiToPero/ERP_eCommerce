<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, SoftDeletes {
        SoftDeletes::restore as private restoreCategory;
    }

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Category $category): void {
            if ($category->isForceDeleting()) {
                return;
            }

            foreach ($category->children()->get() as $child) {
                $child->deleted_by_category_id = $category->getKey();
                if (! $child->save() || ! $child->delete()) {
                    throw new RuntimeException('No se pudo eliminar la subcategoría.');
                }
            }
        });

        static::restoring(function (Category $category): void {
            $category->deleted_by_category_id = null;
        });

        static::restored(function (Category $category): void {
            $children = $category->children()->onlyTrashed()
                ->where('deleted_by_category_id', $category->getKey())
                ->get();

            foreach ($children as $child) {
                if (! $child->restore()) {
                    throw new RuntimeException('No se pudo restaurar la subcategoría.');
                }
            }
        });
    }

    public function delete()
    {
        return $this->getConnection()->transaction(fn () => parent::delete());
    }

    public function restore()
    {
        return $this->getConnection()->transaction(fn () => $this->restoreCategory());
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
