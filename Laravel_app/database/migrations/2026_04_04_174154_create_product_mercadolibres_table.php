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
        Schema::create('product_mercadolibres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();

            // Estado sincronización
            $table->boolean('is_active')->default(false)->comment('Indica si se publica en MercadoLibre');
            $table->enum('status', ['draft','pending','active','paused','error'])->default('draft')->comment('Estado interno');
            $table->string('ml_id')->nullable()->comment('ID del item en MercadoLibre');
            $table->string('ml_url')->nullable()->comment('URL del producto en ML');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_errors')->nullable();

            // REQUIRED
            $table->string('title')->nullable()->comment('Título del producto (REQUIRED)');
            $table->string('category_id')->nullable()->comment('Categoría MercadoLibre (REQUIRED)');
            $table->decimal('price', 12, 2)->nullable()->comment('Precio (REQUIRED)');
            $table->string('currency_id', 3)->default('ARS')->comment('Moneda (REQUIRED)');
            $table->integer('available_quantity')->nullable()->comment('Stock disponible (REQUIRED)');
            $table->enum('buying_mode', ['buy_it_now'])->default('buy_it_now')->comment('Modo de compra');
            $table->string('listing_type_id')->nullable()->comment('Tipo de publicación (REQUIRED)');

            //  CONDICIONALES
            $table->json('attributes')->nullable()->comment('Atributos dinámicos según categoría (CRÍTICO)');

            //  RECOMENDADOS
            $table->json('sale_terms')->nullable()->comment('Términos de venta (warranty, etc)');
            $table->json('shipping')->nullable()->comment('Configuración de envío');
            $table->string('warranty')->nullable()->comment('Garantía');
            $table->json('variations')->nullable()->comment('Variantes del producto');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
            $table->index('ml_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_mercadolibres');
    }
};
