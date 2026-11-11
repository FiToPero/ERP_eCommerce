<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path')->unique();
            $table->unsignedInteger('position');
            $table->string('alt_text')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'position']);
        });

        DB::table('product_details')->whereNotNull('product_details')->orderBy('id')
            ->chunkById(100, function ($records): void {
                foreach ($records as $record) {
                    $details = json_decode($record->product_details, true, 512, JSON_THROW_ON_ERROR);

                    if (! is_array($details)) {
                        throw new UnexpectedValueException("Invalid product_details for record {$record->id}");
                    }

                    $filtered = array_values(array_filter(
                        $details,
                        fn ($detail) => ! is_array($detail) || ($detail['section'] ?? null) !== 'Media' || ($detail['attribute_name'] ?? null) !== 'Main image'
                    ));

                    if (count($filtered) !== count($details)) {
                        DB::table('product_details')->where('id', $record->id)->update([
                            'product_details' => json_encode($filtered, JSON_THROW_ON_ERROR),
                        ]);
                    }
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_link', 'additional_image_links']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_link', 2000)->nullable();
            $table->json('additional_image_links')->nullable();
        });

        Schema::dropIfExists('product_images');
    }
};
