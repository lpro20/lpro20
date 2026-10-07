<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleService
{
    public function complete(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {

            $sale = Sale::query()
                ->with('items.productVariant')
                ->lockForUpdate()
                ->findOrFail($sale->id);

            /*
             * Une vente déjà terminée ne peut pas être
             * traitée une deuxième fois.
             */
            if ($sale->status === SaleStatus::COMPLETED) {
                throw new RuntimeException(
                    'Cette vente est déjà terminée.'
                );
            }

            if ($sale->status === SaleStatus::CANCELLED) {
                throw new RuntimeException(
                    'Une vente annulée ne peut pas être terminée.'
                );
            }

            if ($sale->items->isEmpty()) {
                throw new RuntimeException(
                    'Impossible de terminer une vente sans produit.'
                );
            }

            $subtotal = 0;

            /*
             * Vérification et calcul des lignes.
             */
            foreach ($sale->items as $item) {

                $quantity = (int) $item->quantity;
                $unitPrice = (float) $item->unit_price;
                $costPrice = (float) $item->productVariant->purchase_price;

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'La quantité vendue doit être supérieure à zéro.'
                    );
                }

                if ($unitPrice < 0) {
                    throw new RuntimeException(
                        'Le prix de vente ne peut pas être négatif.'
                    );
                }

                if ($costPrice < 0) {
                    throw new RuntimeException(
                        'Le prix d’achat ne peut pas être négatif.'
                    );
                }

                $lineTotal = $quantity * $unitPrice;
                $lineProfit = ($unitPrice - $costPrice) * $quantity;

                $item->update([
                    'cost_price' => $costPrice,
                    'total' => $lineTotal,
                    'profit' => $lineProfit,
                ]);

                $subtotal += $lineTotal;
            }

            $discount = (float) $sale->discount;

            if ($discount < 0) {
                throw new RuntimeException(
                    'La remise ne peut pas être négative.'
                );
            }

            if ($discount > $subtotal) {
                throw new RuntimeException(
                    'La remise ne peut pas dépasser le sous-total.'
                );
            }

            $total = $subtotal - $discount;

            $amountPaid = (float) $sale->amount_paid;

            if ($amountPaid < 0) {
                throw new RuntimeException(
                    'Le montant payé ne peut pas être négatif.'
                );
            }

            if ($amountPaid < $total) {
                throw new RuntimeException(
                    'Le montant payé est insuffisant.'
                );
            }

            $changeAmount = $amountPaid - $total;

            /*
             * On utilise l'utilisateur actuellement connecté
             * comme responsable de l'encaissement.
             */
            $sale->update([
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'total' => $total,
                'change_amount' => $changeAmount,
                'status' => SaleStatus::COMPLETED,
            ]);

            /*
             * Déduction du stock.
             */
            $stockService = app(StockService::class);

            foreach ($sale->items as $item) {

                $stockService->remove(
                    variant: $item->productVariant,
                    quantity: (int) $item->quantity,
                    type: StockMovementType::SALE,
                    user: Auth::user(),
                    reference: $sale->reference,
                    reason: 'Vente ' . $sale->reference,
                );
            }

            return $sale->fresh([
                'customer',
                'user',
                'items.productVariant.product',
            ]);
        });
    }
}
