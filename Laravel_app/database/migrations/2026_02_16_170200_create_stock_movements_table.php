<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('storage_id')->constrained('storages')->cascadeOnDelete();

            $table->string('direction', 3); // in | out
            $table->string('type', 32); // purchase, sale, adjust, transfer_in, transfer_out
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();

            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamp('moved_at')->useCurrent();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'storage_id']);
            $table->index(['product_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
