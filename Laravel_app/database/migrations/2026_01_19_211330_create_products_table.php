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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique()->comment('Stock Keeping Unit, identificador único del producto. INTERNO');
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->text('description')->nullable();
            $table->string('brand')->nullable()->comment('Marca del producto (REQUIRED IF APPLICABLE)');
            $table->string('barcode')->nullable()->comment('Código global GTIN/EAN/UPC (REQUIRED IF APPLICABLE)');
            $table->string('mpn')->nullable()->comment('Código del fabricante (REQUIRED IF APPLICABLE)');
            $table->boolean('identifier_exists')->default(true)->comment('Indica si existen identificadores validos (brand/gtin/mpn)');
            $table->enum('condition', ['new', 'refurbished', 'used'])->default('new')->comment('Estado del producto (REQUIRED IF APPLICABLE)');
            $table->timestamp('availability_date')->nullable()->comment('Fecha disponibilidad (REQUIRED si preorder)');
            $table->string('image_link', 2000)->nullable()->comment('Imagen principal del producto (REQUIRED)');
            $table->json('additional_image_links')->nullable()->comment('Imágenes adicionales (RECOMMENDED)');
            $table->string('video_link', 2000)->nullable()->comment('Video del producto (RECOMMENDED)');


            // Resumen de Requerimientos google, Mercado Libre, Meta y WEB
            $table->json('web_requirements')->nullable()
                ->default('{"required_fields": ["name", "slug", "short_description", "description", "brand", "sku", "barcode", "mpn", "condition"], "conditional_fields": ["availability_date"], "recommended_fields": ["category_id"]}')
                ->comment('Resumen de requerimientos para cumplir con el sitio web. NOTE: esto lo manejamos nosotros');
            $table->json('google_requirements')->nullable()
                ->default('{"required_fields": ["title", "description", "link", "image_link", "price", "currency", "availability"], "conditional_fields": ["brand", "gtin", "mpn", "condition", "availability_date"], "recommended_fields": ["google_product_category", "product_type", "additional_image_links", "sale_price", "sale_price_start", "sale_price_end", "item_group_id"]}')
                ->comment('Resumen de requerimientos para cumplir con Google Merchant');
            $table->json('ml_requirements')->nullable()
                ->default('{"required_fields": ["title", "category_id", "price", "currency_id", "available_quantity", "listing_type_id", "condition", "pictures"], "conditional_fields": ["brand", "gtin", "attributes"], "recommended_fields": ["sale_terms", "shipping", "video_id", "warranty", "variations"]}')
                ->comment('Resumen de requerimientos para cumplir con MercadoLibre. NOTE OJO "Attributes" son dinamicos. Se guarda atributos según cada categoría de Mercado Libre. No siempre son los mismos Atributos');
            $table->json('meta_requirements')->nullable()
                ->default('{"required_fields": ["title", "description", "availability", "condition", "price", "link", "image_link"], "conditional_fields": ["brand", "gtin", "mpn"], "recommended_fields": ["additional_image_links", "item_group_id", "color", "size", "google_product_category", "product_type", "sale_price", "sale_price_start", "sale_price_end"]}')
                ->comment('Resumen de requerimientos para cumplir con Meta.');


            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
