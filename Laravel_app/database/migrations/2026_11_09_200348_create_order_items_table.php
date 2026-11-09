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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // Puede quedar NULL si el producto se elimina
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // Fotografía del producto
            $table->string('product_name', 255);
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            // Cantidad
            $table->unsignedInteger('quantity');
            // Importes
            $table->decimal('unit_price', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('total', 14, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
