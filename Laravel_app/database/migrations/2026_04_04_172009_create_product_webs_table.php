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
        Schema::create('product_webs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();

            // Estado de sincronización
            $table->boolean('is_active')->default(false)
                ->comment('Indica si el producto está habilitado para enviarse a la web');
            $table->enum('status', ['draft', 'pending', 'active', 'disapproved'])->default('draft')
                ->comment('Estado interno: draft, pending, active, disapproved');

            // REQUIRED
            $table->string('link', 2000)->nullable()
                ->comment('URL de la pagina del producto en tu tienda (REQUIRED)');
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_webs');
    }
};
