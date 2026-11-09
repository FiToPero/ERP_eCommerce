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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete();
            $table->string('order_number', 50)->unique();
            // Estados independientes
            $table->enum('status', ['pending', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'cancelled', 'returned', 'delivered', 'refunded'])->default('pending');

            $table->string('payment_status', 30)->default('unpaid');

            $table->string('shipping_status', 30)->default('pending');

            // Address Order
            $table->string('shipping_address', 255)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_postcode', 20)->nullable();
            $table->string('shipping_country', 100)->nullable();

            // Moneda
            $table->char('currency', 3)->default('ARS');
            // Importes
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            // Notas
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Fechas
            $table->timestamp('pending_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('ready_to_ship_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['payment_status']);
            $table->index(['shipping_status']);
            $table->index(['pending_at']);
            // $table->index(['confirmed_at']);
            // $table->index(['processing_at']);
            // $table->index(['ready_to_ship_at']);
            // $table->index(['shipped_at']);
            // $table->index(['returned_at']);
            // $table->index(['delivered_at']);
            // $table->index(['cancelled_at']);
            // $table->index(['refunded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
