<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $iphone  = Category::where('slug', 'iphone')->first();
        $samsung = Category::where('slug', 'samsung-galaxy')->first();
        $laptops = Category::where('slug', 'laptops')->first();

        if ($iphone) {
            Product::create([
                'category_id'       => $iphone->id,
                'name'              => 'iPhone 15 Pro',
                'sku'               => 'IP15PRO-001',
                'slug'              => 'iphone-15-pro',
                'short_description' => 'El último iPhone con chip A17 Pro',
                'description'       => 'iPhone 15 Pro con pantalla Super Retina XDR de 6.1 pulgadas, chip A17 Pro, cámara principal de 48MP y diseño de titanio.',
                'brand'             => 'Apple',
                'barcode'           => '0190199559574',
                'mpn'               => 'MTXT3LL/A',
                'identifier_exists' => true,
                'condition'         => 'new',
                'availability_date' => null,
            ]);

            Product::create([
                'category_id'       => $iphone->id,
                'name'              => 'iPhone 14',
                'sku'               => 'IP14-001',
                'slug'              => 'iphone-14',
                'short_description' => 'iPhone 14 con chip A15 Bionic',
                'description'       => 'iPhone 14 con pantalla de 6.1 pulgadas, chip A15 Bionic y sistema de cámara dual avanzado.',
                'brand'             => 'Apple',
                'barcode'           => '0195949729085',
                'mpn'               => 'MPWH3LL/A',
                'identifier_exists' => true,
                'condition'         => 'new',
                'availability_date' => null,
            ]);
        }

        if ($samsung) {
            Product::create([
                'category_id'       => $samsung->id,
                'name'              => 'Samsung Galaxy S24 Ultra',
                'sku'               => 'SGS24U-001',
                'slug'              => 'galaxy-s24-ultra',
                'short_description' => 'El flagship de Samsung con S Pen',
                'description'       => 'Galaxy S24 Ultra con pantalla Dynamic AMOLED 2X de 6.8", Snapdragon 8 Gen 3, cámara de 200MP y S Pen integrado.',
                'brand'             => 'Samsung',
                'barcode'           => '8806095364861',
                'mpn'               => 'SM-S928BZKPEUB',
                'identifier_exists' => true,
                'condition'         => 'new',
                'availability_date' => null,
            ]);
        }

        if ($laptops) {
            Product::create([
                'category_id'       => $laptops->id,
                'name'              => 'MacBook Pro 14"',
                'sku'               => 'MBP14-001',
                'slug'              => 'macbook-pro-14',
                'short_description' => 'MacBook Pro con chip M3 Pro',
                'description'       => 'MacBook Pro de 14 pulgadas con chip M3 Pro, 18GB de RAM, SSD de 512GB y pantalla Liquid Retina XDR.',
                'brand'             => 'Apple',
                'barcode'           => '195949628503',
                'mpn'               => 'MRX33LL/A',
                'identifier_exists' => true,
                'condition'         => 'new',
                'availability_date' => null,
            ]);

            Product::create([
                'category_id'       => $laptops->id,
                'name'              => 'Dell XPS 15',
                'sku'               => 'DXPS15-001',
                'slug'              => 'dell-xps-15',
                'short_description' => 'Laptop premium con Intel Core i7',
                'description'       => 'Dell XPS 15 con procesador Intel Core i7-13700H, 16GB RAM, SSD 512GB, pantalla 15.6" FHD+.',
                'brand'             => 'Dell',
                'barcode'           => '884116346273',
                'mpn'               => 'XPS9530-7759SLV-PUS',
                'identifier_exists' => true,
                'condition'         => 'new',
                'availability_date' => null,
            ]);
        }
    }
}
