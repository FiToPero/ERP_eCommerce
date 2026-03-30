<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('api_url')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('channels')->insert([
            [
                'name' => 'Google Shopping',
                'code' => 'google',
                'slug' => 'google-shopping',
                'description' => 'Google Merchant Center / Google Shopping Feed',
                'api_url' => 'https://merchantcenter.google.com',
                'config' => json_encode([
                    'required_fields' => ['gtin', 'mpn', 'condition', 'availability']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meta Catalog',
                'code' => 'meta',
                'slug' => 'meta-catalog',
                'description' => 'Facebook & Instagram Product Catalog',
                'api_url' => 'https://graph.facebook.com',
                'config' => json_encode([
                    'required_fields' => ['availability', 'condition']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MercadoLibre',
                'code' => 'meli',
                'slug' => 'mercadolibre',
                'description' => 'MercadoLibre Marketplace',
                'api_url' => 'https://api.mercadolibre.com',
                'config' => json_encode([
                    'required_fields' => ['listing_type', 'category_id']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cap erp ecommer',
                'code' => 'cap',
                'slug' => 'cap-erp-ecommer',
                'description' => 'CAP ERP E-commerce My',
                'api_url' => 'http://cap-erp-ecommer.com',
                'config' => json_encode([
                    'required_fields' => ['lived', 'category_id']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
