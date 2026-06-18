<?php

namespace App\Observers;

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        DB::transaction(function () use ($stockMovement) {
            $productDetail = $stockMovement->productDetail()->lockForUpdate()->first();

            if (! $productDetail) {
                Log::error("Product detail not found for StockMovement ID: {$stockMovement->id}");
                return;
            }

            if ($stockMovement->quantity <= 0) {
                throw new \InvalidArgumentException("Quantity must be greater than zero for StockMovement ID: {$stockMovement->id}");
            }
        });
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "restored" event.
     */
    public function restored(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "force deleted" event.
     */
    public function forceDeleted(StockMovement $stockMovement): void
    {
        //
    }
}
