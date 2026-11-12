<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path')->unique();
            $table->unsignedInteger('position');
            $table->string('filename_description', 100);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'position']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
