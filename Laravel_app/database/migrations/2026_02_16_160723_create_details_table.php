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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Identification
            $table->string('model')->nullable();
            
            // Physical attributes
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('weight_unit')->default('kg'); // kg, lb, g, oz
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->string('dimensions_unit')->default('cm'); // cm, in, m
            
            // Appearance
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            
            // Manufacturing
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('origin')->nullable(); // Country of origin
            $table->year('year')->nullable(); // Year of manufacture/model
            
            // Warranty
            $table->integer('warranty_period')->nullable(); // in months
            $table->text('warranty_details')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
