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
        /*
         * Registra los eventos del modelo que preparan y ejecutan la limpieza
         * de archivos cuando una categoría se elimina permanentemente.
         *
         * forceDeleting ocurre antes de borrar la fila de la categoría, por lo
         * que todavía podemos consultar sus subcategorías y productos. Se
         * recorren las subcategorías por niveles, empezando por la categoría
         * actual; withTrashed() incluye también las categorías ya eliminadas
         * lógicamente. $ids conserva todos los identificadores encontrados y
         * whereNotIn evita volver a agregarlos al recorrido.
         *
         * Con esos identificadores se buscan los productos relacionados,
         * incluidos los que tienen borrado lógico, y se cargan sus imágenes y
         * videos. Se guardan temporalmente como relaciones en la instancia para
         * que sigan disponibles después de que se eliminen las filas asociadas.
         *
         * forceDeleted ocurre después del borrado permanente. En ese momento se
         * programa la eliminación de los archivos asociados a cada imagen y
         * video. Los métodos invocados la ejecutan después de confirmar la
         * transacción y verifican que el archivo ya no esté referenciado antes
         * de borrarlo, evitando perder archivos si la transacción se revierte o
         * si todavía los usa otro registro.
         */
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

            $videos = ProductVideo::query()
                ->whereIn('product_id', Product::withTrashed()->whereIn('category_id', $ids)->select('id'))
                ->get();

            $category->setRelation('videosToDelete', $videos);
        });

        static::forceDeleted(function (Category $category): void {
            foreach ($category->getRelation('imagesToDelete') as $image) {
                ProductImage::deleteFileAfterCommit($image->path, $category);
            }
            foreach ($category->getRelation('videosToDelete') as $video) {
                ProductVideo::deleteFilesAfterCommit($video->path, $category);
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
