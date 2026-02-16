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
        Schema::create('product_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            
            // Custom overrides (optional, override product defaults)
            $table->string('custom_title')->nullable();
            $table->text('custom_description')->nullable();
            $table->decimal('custom_price', 10, 2)->nullable();
            
            // Channel-specific data (JSON for flexibility)
            $table->json('metadata')->nullable();
            
            // Sync information
            $table->timestamp('last_synced_at')->nullable();
            $table->string('external_id')->nullable(); // ID en el canal externo
            $table->string('external_url')->nullable(); // URL del producto en el canal
            
            $table->timestamps();
            $table->softDeletes();
            
            // Un producto solo puede estar una vez por canal
            $table->unique(['product_id', 'channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_channels');
    }
};
