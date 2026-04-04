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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();

            // ─── Variantes (Apparel & variantes en general) ──────────────────     
            $table->string('color', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->enum('size_type', ['regular', 'petite', 'maternity', 'big', 'tall', 'plus'])->nullable();
            $table->enum('size_system', ['US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU'])->nullable();
            $table->string('material', 200)->nullable();
            $table->string('pattern', 100)->nullable();
            $table->enum('gender', ['male', 'female', 'unisex'])->nullable();
            $table->enum('age_group', ['newborn', 'infant', 'toddler', 'kids', 'adult'])->nullable();

            // ─── Dimensiones físicas del producto ────────────────────────────
            $table->decimal('product_length', 8, 2)->nullable();
            $table->decimal('product_width', 8, 2)->nullable();
            $table->decimal('product_height', 8, 2)->nullable();
            $table->decimal('product_weight', 8, 2)->nullable();
            $table->enum('product_dimension_unit', ['cm', 'in'])->default('cm');
            $table->enum('product_weight_unit', ['kg', 'lb', 'g', 'oz'])->default('kg');

            // ─── Especificaciones / Highlights (JSON estructurado) ───────────
            $table->json('product_details')->nullable();            // array de {section, attribute_name, attribute_value}
            $table->json('product_highlights')->nullable();         // array de strings (hasta 100)

            // Labels personalizados para campañas
            $table->string('custom_label_0')->nullable();
            $table->string('custom_label_1')->nullable();
            $table->string('custom_label_2')->nullable();
            $table->string('custom_label_3')->nullable();
            $table->string('custom_label_4')->nullable();

            // Flags
            $table->boolean('is_adult')->default(false)->comment('Producto para adultos');
            $table->boolean('is_bundle')->default(false)->comment('Indica si es un bundle');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
