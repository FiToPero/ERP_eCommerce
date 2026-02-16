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
            $table->string('name'); // 'Google Shopping', 'Meta Catalog', 'MercadoLibre'
            $table->string('code')->unique(); // 'google', 'meta', 'meli'
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable(); // Configuración específica del canal
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar los canales iniciales
        DB::table('channels')->insert([
            [
                'name' => 'Google Shopping',
                'code' => 'google',
                'slug' => 'google-shopping',
                'description' => 'Google Merchant Center / Google Shopping Feed',
                'is_active' => true,
                'config' => json_encode([
                    'api_url' => 'https://merchantcenter.google.com',
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
                'is_active' => true,
                'config' => json_encode([
                    'api_url' => 'https://graph.facebook.com',
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
                'is_active' => true,
                'config' => json_encode([
                    'api_url' => 'https://api.mercadolibre.com',
                    'required_fields' => ['listing_type', 'category_id']
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
