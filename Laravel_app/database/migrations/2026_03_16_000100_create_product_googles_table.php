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
        Schema::create('product_googles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();

            // Estado de sincronización
            $table->boolean('is_active')->default(false)
                ->comment('Indica si el producto está habilitado para enviarse a Google');
            $table->enum('status', ['draft', 'pending', 'active', 'disapproved'])->default('draft')
                ->comment('Estado interno: draft, pending, active, disapproved');
            $table->string('google_id')->nullable()
                ->comment('ID asignado por Google Merchant');
            $table->string('google_url', 2000)->nullable()
                ->comment('URL del producto en Google Shopping');
            $table->timestamp('last_synced_at')->nullable()
                ->comment('Última sincronización con Google');
            $table->json('sync_errors')->nullable()
                ->comment('Errores devueltos por la API de Google');

            // REQUIRED
            $table->string('title', 150)->nullable()
                ->comment('Título del producto (REQUIRED)');
            $table->text('description')->nullable()
                ->comment('Descripción del producto (REQUIRED)');
            $table->string('link', 2000)->nullable()
                ->comment('URL de la pagina del producto en tu tienda (REQUIRED)');
            $table->decimal('price', 12, 2)->nullable()
                ->comment('Precio del producto (REQUIRED)');
            $table->char('currency', 3)->default('USD')
                ->comment('Moneda ISO 4217 (REQUIRED)');
            $table->enum('availability', ['in_stock','out_of_stock','preorder','backorder'])->default('in_stock')
                ->comment('Disponibilidad del producto (REQUIRED)');

            // Recomendados
            $table->string('google_product_category')->nullable()
                ->comment('Categoría oficial de Google (RECOMMENDED)');
            $table->string('product_type', 750)->nullable()
                ->comment('Categoría propia del comercio. NOTA: deberia crearce automaticamente de categories (RECOMMENDED)');
            $table->decimal('sale_price', 12, 2)->nullable()
                ->comment('Precio en oferta (RECOMMENDED)');
            $table->timestamp('sale_price_start')->nullable()
                ->comment('Inicio de oferta');
            $table->timestamp('sale_price_end')->nullable()
                ->comment('Fin de oferta');
            $table->string('item_group_id')->nullable()
                ->comment('ID de grupo de variantes, mismo valor para variantes del mismo producto. ej: color (RECOMMENDED)'); 

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
            $table->index('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_googles');
    }
};
