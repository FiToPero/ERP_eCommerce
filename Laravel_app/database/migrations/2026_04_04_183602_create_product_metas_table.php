<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();

            // Estado sincronización
            $table->boolean('is_active')->default(false)->comment('Indica si el producto se publica en Meta');
            $table->enum('status', ['draft','pending','active','rejected'])->default('draft')->comment('Estado interno de sincronización');
            $table->string('meta_id')->nullable()->comment('ID del producto en Meta Catalog');
            $table->string('permalink')->nullable()->comment('URL del producto en Facebook/Instagram');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_errors')->nullable()->comment('Errores devueltos por Meta');
            
            // REQUIRED
            $table->string('title')->nullable()->comment('Nombre del producto (REQUIRED)');
            $table->text('description')->nullable()->comment('Descripción (REQUIRED)');
            $table->string('availability')->nullable()->comment('Disponibilidad (REQUIRED)');
            $table->enum('condition', ['new','used','refurbished'])->default('new')->comment('Condición (REQUIRED)');
            $table->string('price')->nullable()->comment('Precio con moneda, ej: 100 ARS (REQUIRED)');
            $table->string('link', 2000)->nullable()->comment('URL del producto (REQUIRED)');

            // RECOMMENDED
            $table->string('item_group_id')->nullable()->comment('Grupo de variantes (RECOMMENDED)');
            $table->string('color')->nullable()->comment('Color (RECOMMENDED si hay variantes)');
            $table->string('size')->nullable()->comment('Tamaño (RECOMMENDED si aplica)');
            $table->string('google_product_category')->nullable()->comment('Categoría Google (RECOMMENDED por Meta)');
            $table->string('product_type')->nullable()->comment('Categoría propia (RECOMMENDED)');
            $table->decimal('sale_price', 12, 2)->nullable()->comment('Precio oferta (RECOMMENDED)');
            $table->timestamp('sale_price_start')->nullable()->comment('Inicio de la oferta (RECOMMENDED)');
            $table->timestamp('sale_price_end')->nullable()->comment('Fin de la oferta (RECOMMENDED)');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('meta_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_metas');
    }
};

