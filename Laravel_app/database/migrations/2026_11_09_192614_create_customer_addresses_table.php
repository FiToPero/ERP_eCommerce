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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            // Identificación de la dirección
            $table->string('name', 100)->nullable();

            // Tipo de dirección
            $table->enum('type', ['shipping', 'billing', 'both'])->default('shipping');

            // Datos del destinatario
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 30)->nullable();

            // Dirección
            $table->string('country', 100)->default('Argentina');
            $table->string('state', 100)->nullable();
            $table->string('city', 100);
            $table->string('postal_code', 20);

            $table->string('street', 150);
            $table->string('street_number', 20)->nullable();

            $table->string('floor', 20)->nullable();
            $table->string('apartment', 20)->nullable();

            $table->text('additional_information')->nullable();

            // Geolocalización opcional
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Dirección predeterminada
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->softDeletes();

            $table->index(['customer_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
