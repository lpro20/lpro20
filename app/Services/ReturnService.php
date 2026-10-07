<?php

namespace App\Services;

use App\Enums\ReturnStatus;
use App\Enums\StockMovementType;
use App\Models\ProductReturn;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    public function complete(ProductReturn $return): ProductReturn
    {
        return DB::transaction(function () use ($return) {

            $return = ProductReturn::query()
                ->with([
                    'sale',
                    'saleItem',
                    'productVariant',
                ])
                ->lockForUpdate()
                ->findOrFail($return->id);

            if (!$return->sale) {
                throw new RuntimeException(
                    'La vente associée à ce retour est introuvable.'
                );
            }

            if ($return->sale->status !== \App\Enums\SaleStatus::COMPLETED) {
                throw new RuntimeException(
                    'Seules les ventes terminées peuvent faire l’objet d’un retour.'
                );
            }

            if ($return->status === ReturnStatus::COMPLETED) {
                throw new RuntimeException(
                    'Ce retour a déjà été traité.'
                );
            }

            if ($return->status === ReturnStatus::CANCELLED) {
                throw new RuntimeException(
                    'Ce retour a été annulé.'
                );
            }

            if ($return->quantity <= 0) {
                throw new RuntimeException(
                    'La quantité retournée doit être supérieure à zéro.'
                );
            }

            $saleItem = SaleItem::query()
                ->lockForUpdate()
                ->findOrFail($return->sale_item_id);

            /*
             * Vérification : l'article retourné doit
             * correspondre à la ligne de vente.
             */
            if (
                (int) $saleItem->product_variant_id
                !== (int) $return->product_variant_id
            ) {
                throw new RuntimeException(
                    'L’article retourné ne correspond pas à la vente.'
                );
            }

            /*
             * Quantité déjà retournée.
             */
            $alreadyReturned = ProductReturn::query()
                ->where('sale_item_id', $saleItem->id)
                ->where(
                    'status',
                    ReturnStatus::COMPLETED->value
                )
                ->sum('quantity');

            $soldQuantity = (int) $saleItem->quantity;

            $requestedQuantity = (int) $return->quantity;

            $remainingQuantity = $soldQuantity - (int) $alreadyReturned;

            if ($requestedQuantity <= 0) {
                throw new RuntimeException(
                    'La quantité retournée doit être supérieure à zéro.'
                );
            }

            if ($requestedQuantity > $remainingQuantity) {
                throw new RuntimeException(
                    "Retour impossible. "
                        . "Quantité vendue : {$soldQuantity}. "
                        . "Déjà retournée : {$alreadyReturned}. "
                        . "Quantité encore retournable : {$remainingQuantity}. "
                        . "Quantité demandée : {$requestedQuantity}."
                );
            }

            /*
             * Si l'article est revendable,
             * on le remet dans le stock.
             */
            if ($return->restock) {

                app(StockService::class)->add(
                    variant: $return->productVariant,
                    quantity: (int) $return->quantity,
                    type: StockMovementType::RETURN,
                    user: Auth::user(),
                    reference: $return->reference,
                    reason: 'Retour client ' . $return->reference,
                );
            }

            $return->update([
                'status' => ReturnStatus::COMPLETED,
                'user_id' => Auth::id(),
            ]);

            return $return->fresh([
                'sale',
                'saleItem',
                'productVariant.product',
                'user',
            ]);
        });
    }
}
