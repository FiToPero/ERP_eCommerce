<?php
namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getCategoryTree()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        // Organizar las categorías por ID
        $categoriesById = $categories->keyBy('id');

        // Preparar cada categoría con un array children
        foreach ($categoriesById as $category) {
            $category->setRelation('children', collect());
        }

        // Construir el árbol
        $tree = collect();

        foreach ($categoriesById as $category) {

            if ($category->parent_id === null) {

                // Es una categoría principal
                $tree->push($category);

            } else {

                // Buscar su categoría padre
                $parent = $categoriesById->get($category->parent_id);

                if ($parent) {
                    $parent->children->push($category);
                }
            }
        }

        return $tree->values();
    }
}