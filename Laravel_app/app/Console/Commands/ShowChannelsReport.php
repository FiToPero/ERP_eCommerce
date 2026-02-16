<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Channel;
use App\Models\ProductChannel;
use Illuminate\Console\Command;

class ShowChannelsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:report {--product= : Mostrar detalles de un producto específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra un reporte de productos publicados en canales de venta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('product');

        if ($productId) {
            return $this->showProductDetails($productId);
        }

        $this->showGeneralReport();
    }

    /**
     * Show general report.
     */
    protected function showGeneralReport()
    {
        $this->info('📊 REPORTE GENERAL DE CANALES DE VENTA');
        $this->newLine();

        // Resumen por canal
        $channels = Channel::withCount(['productChannels', 'products'])->get();
        
        $this->info('🔷 Canales disponibles:');
        $channelData = [];
        
        foreach ($channels as $channel) {
            $active = ProductChannel::where('channel_id', $channel->id)
                ->where('is_active', true)
                ->count();
                
            $published = ProductChannel::where('channel_id', $channel->id)
                ->whereNotNull('published_at')
                ->count();

            $channelData[] = [
                'Canal' => $channel->name,
                'Código' => $channel->code,
                'Total' => $channel->product_channels_count,
                'Activos' => $active,
                'Publicados' => $published,
                'Estado' => $channel->is_active ? '✅ Activo' : '❌ Inactivo',
            ];
        }
        
        $this->table(
            ['Canal', 'Código', 'Total', 'Activos', 'Publicados', 'Estado'],
            $channelData
        );

        $this->newLine();

        // Top 5 productos con más canales
        $this->info('⭐ Top 5 productos con más canales:');
        $topProducts = Product::withCount('channels')
            ->orderBy('channels_count', 'desc')
            ->take(5)
            ->get();

        $topData = [];
        foreach ($topProducts as $product) {
            $topData[] = [
                'ID' => $product->id,
                'Producto' => substr($product->name, 0, 40),
                'Canales' => $product->channels_count,
                'Precio Base' => '$' . number_format($product->price, 2),
            ];
        }

        $this->table(
            ['ID', 'Producto', 'Canales', 'Precio Base'],
            $topData
        );

        $this->newLine();

        // Productos que necesitan sincronización
        $needsSync = ProductChannel::where('is_active', true)
            ->where(function($q) {
                $q->where('last_synced_at', '<', now()->subDays(7))
                  ->orWhereNull('last_synced_at');
            })
            ->count();

        $this->warn("⚠️  Productos que necesitan sincronización: {$needsSync}");
        
        $this->newLine();
        $this->info('💡 Tip: Usa --product=ID para ver detalles de un producto específico');
    }

    /**
     * Show specific product details.
     */
    protected function showProductDetails($productId)
    {
        $product = Product::with(['channels', 'productChannels.channel'])->find($productId);

        if (!$product) {
            $this->error("❌ Producto con ID {$productId} no encontrado");
            return 1;
        }

        $this->info("📦 DETALLES DE: {$product->name}");
        $this->newLine();

        $this->line("ID: {$product->id}");
        $this->line("Slug: {$product->slug}");
        $this->line("Precio base: \${$product->price}");
        $this->line("Stock: {$product->stock}");
        $this->newLine();

        if ($product->productChannels->isEmpty()) {
            $this->warn('Este producto no está publicado en ningún canal.');
            return 0;
        }

        $this->info('🔷 Publicación en canales:');
        
        foreach ($product->productChannels as $pc) {
            $this->newLine();
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Canal: {$pc->channel->name} ({$pc->channel->code})");
            $this->line("Estado: " . ($pc->is_active ? '✅ Activo' : '❌ Inactivo'));
            $this->line("Publicado: " . ($pc->published_at ? $pc->published_at->format('Y-m-d H:i') : 'No publicado'));
            
            if ($pc->custom_title) {
                $this->line("Título custom: {$pc->custom_title}");
            }
            
            if ($pc->custom_price) {
                $this->line("Precio custom: \${$pc->custom_price} (base: \${$product->price})");
            }

            if ($pc->external_id) {
                $this->line("ID externo: {$pc->external_id}");
            }

            if ($pc->last_synced_at) {
                $daysSince = now()->diffInDays($pc->last_synced_at);
                $syncStatus = $daysSince > 7 ? '⚠️ ' : '✅ ';
                $this->line("Última sync: {$syncStatus}{$pc->last_synced_at->format('Y-m-d H:i')} ({$daysSince} días)");
            } else {
                $this->warn("  Última sync: ⚠️  Nunca sincronizado");
            }

            // Mostrar algunos campos de metadata
            if ($pc->metadata && is_array($pc->metadata)) {
                $this->line("\n  Metadata destacada:");
                
                $importantFields = ['gtin', 'condition', 'availability', 'listing_type', 'warranty'];
                foreach ($importantFields as $field) {
                    if (isset($pc->metadata[$field])) {
                        $this->line("    • {$field}: {$pc->metadata[$field]}");
                    }
                }
            }
        }

        $this->newLine();
        
        return 0;
    }
}
