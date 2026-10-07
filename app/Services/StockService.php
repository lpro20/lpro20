<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /**
     * Ajouter du stock.
     */
    public function add(
        ProductVariant $variant,
        int $quantity,
        StockMovementType $type,
        ?User $user = null,
        ?string $reference = null,
        ?string $reason = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'La quantité doit être supérieure à zéro.'
            );
        }

        if (!$type->isIncoming()) {
            throw new RuntimeException(
                'Ce type de mouvement ne correspond pas à une entrée de stock.'
            );
        }

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $user,
            $reference,
            $reason
        ) {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->id);

            $stockBefore = $variant->stock_quantity;

            $variant->stock_quantity += $quantity;

            $variant->save();

            return StockMovement::create([
                'product_variant_id' => $variant->id,
                'user_id' => $user?->id,
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $variant->stock_quantity,
                'reference' => $reference,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Retirer du stock.
     */
    public function remove(
        ProductVariant $variant,
        int $quantity,
        StockMovementType $type,
        ?User $user = null,
        ?string $reference = null,
        ?string $reason = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'La quantité doit être supérieure à zéro.'
            );
        }

        if (!$type->isOutgoing()) {
            throw new RuntimeException(
                'Ce type de mouvement ne correspond pas à une sortie de stock.'
            );
        }

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $user,
            $reference,
            $reason
        ) {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->id);

            $stockBefore = $variant->stock_quantity;

            if ($stockBefore < $quantity) {
                throw new RuntimeException(
                    "Stock insuffisant. Stock disponible : {$stockBefore}."
                );
            }

            $variant->stock_quantity -= $quantity;

            $variant->save();

            return StockMovement::create([
                'product_variant_id' => $variant->id,
                'user_id' => $user?->id,
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $variant->stock_quantity,
                'reference' => $reference,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Définir une nouvelle quantité de stock.
     */
    public function adjust(
        ProductVariant $variant,
        int $newQuantity,
        ?User $user = null,
        ?string $reason = null
    ): StockMovement {
        if ($newQuantity < 0) {
            throw new RuntimeException(
                'Le stock ne peut pas être négatif.'
            );
        }

        return DB::transaction(function () use (
            $variant,
            $newQuantity,
            $user,
            $reason
        ) {
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->id);

            $stockBefore = $variant->stock_quantity;

            if ($newQuantity === $stockBefore) {
                throw new RuntimeException(
                    'La nouvelle quantité est identique au stock actuel.'
                );
            }

            $type = $newQuantity > $stockBefore
                ? StockMovementType::ADJUSTMENT_IN
                : StockMovementType::ADJUSTMENT_OUT;

            $quantity = abs($newQuantity - $stockBefore);

            $variant->stock_quantity = $newQuantity;

            $variant->save();

            return StockMovement::create([
                'product_variant_id' => $variant->id,
                'user_id' => $user?->id,
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $newQuantity,
                'reason' => $reason,
            ]);
        });
    }
}