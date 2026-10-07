<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\Support\Facades\Auth;
class PurchaseService
{
    public function receive(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {

            $purchase = Purchase::query()
                ->with('items.productVariant')
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            /*
             * Un achat déjà réceptionné ne peut pas être
             * réceptionné une deuxième fois.
             */
            if ($purchase->status === PurchaseStatus::RECEIVED) {
                throw new RuntimeException(
                    'Cet achat a déjà été réceptionné.'
                );
            }

            if ($purchase->status === PurchaseStatus::CANCELLED) {
                throw new RuntimeException(
                    'Un achat annulé ne peut pas être réceptionné.'
                );
            }

            if ($purchase->items->isEmpty()) {
                throw new RuntimeException(
                    'Impossible de réceptionner un achat sans produit.'
                );
            }

            $subtotal = 0;

            foreach ($purchase->items as $item) {

                $quantity = (int) $item->quantity;

                $unitPrice = (float) $item->unit_price;

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'Une ligne d’achat possède une quantité invalide.'
                    );
                }

                if ($unitPrice < 0) {
                    throw new RuntimeException(
                        'Une ligne d’achat possède un prix invalide.'
                    );
                }

                $lineTotal = $quantity * $unitPrice;

                $item->update([
                    'total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
            }

            $discount = (float) $purchase->discount;

            $shippingCost = (float) $purchase->shipping_cost;

            if ($discount < 0) {
                throw new RuntimeException(
                    'La remise ne peut pas être négative.'
                );
            }

            if ($shippingCost < 0) {
                throw new RuntimeException(
                    'Les frais de livraison ne peuvent pas être négatifs.'
                );
            }

            if ($discount > $subtotal) {
                throw new RuntimeException(
                    'La remise ne peut pas dépasser le sous-total.'
                );
            }

            $total = $subtotal - $discount + $shippingCost;

            $purchase->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => PurchaseStatus::RECEIVED,
            ]);

            /*
             * Maintenant seulement, nous augmentons le stock.
             */
            $stockService = app(StockService::class);

            foreach ($purchase->items as $item) {

                $stockService->add(
                    variant: $item->productVariant,
                    quantity: (int) $item->quantity,
                    type: StockMovementType::PURCHASE,
                    user: Auth::user(),
                    reference: $purchase->reference,
                    reason: 'Réception de l’achat ' . $purchase->reference,
                );
            }

            return $purchase->fresh([
                'supplier',
                'items.productVariant.product',
            ]);
        });
    }
}