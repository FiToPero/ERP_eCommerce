<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías principales (root)
        $electronics = Category::create([
            'name' => 'Electrónica',
            'slug' => 'electronica',
            'description' => 'Productos electrónicos',
        ]);

        $clothing = Category::create([
            'name' => 'Ropa',
            'slug' => 'ropa',
            'description' => 'Ropa y accesorios',
        ]);

        $home = Category::create([
            'name' => 'Hogar',
            'slug' => 'hogar',
            'description' => 'Productos para el hogar',
        ]);

        // Subcategorías de Electrónica
        $smartphones = Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Teléfonos inteligentes',
        ]);

        $laptops = Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => 'Computadoras portátiles',
        ]);

        $tablets = Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Tablets',
            'slug' => 'tablets',
            'description' => 'Tabletas',
        ]);

        // Sub-subcategorías de Smartphones (3er nivel)
        Category::create([
            'parent_id' => $smartphones->id,
            'name' => 'iPhone',
            'slug' => 'iphone',
            'description' => 'Smartphones Apple iPhone',
        ]);

        Category::create([
            'parent_id' => $smartphones->id,
            'name' => 'Samsung Galaxy',
            'slug' => 'samsung-galaxy',
            'description' => 'Smartphones Samsung Galaxy',
        ]);

        // Subcategorías de Ropa
        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Hombre',
            'slug' => 'ropa-hombre',
            'description' => 'Ropa para hombre',
        ]);

        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Mujer',
            'slug' => 'ropa-mujer',
            'description' => 'Ropa para mujer',
        ]);

        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Niños',
            'slug' => 'ropa-ninos',
            'description' => 'Ropa para niños',
        ]);

        // Subcategorías de Hogar
        Category::create([
            'parent_id' => $home->id,
            'name' => 'Muebles',
            'slug' => 'muebles',
            'description' => 'Muebles para el hogar',
        ]);

        Category::create([
            'parent_id' => $home->id,
            'name' => 'Decoración',
            'slug' => 'decoracion',
            'description' => 'Artículos de decoración',
        ]);
    }
}
