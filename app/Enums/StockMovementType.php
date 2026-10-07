<?php

namespace App\Enums;

enum StockMovementType: string
{
    case INITIAL = 'initial';
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN = 'return';
    case DAMAGE = 'damage';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL => 'Stock initial',
            self::PURCHASE => 'Achat',
            self::SALE => 'Vente',
            self::RETURN => 'Retour',
            self::DAMAGE => 'Casse / Perte',
            self::ADJUSTMENT_IN => 'Ajustement entrée',
            self::ADJUSTMENT_OUT => 'Ajustement sortie',
        };
    }

    // public function isIncoming(): bool
    // {
    //     return in_array($this, [
    //         self::INITIAL,
    //         self::PURCHASE,
    //         self::RETURN,
    //         self::ADJUSTMENT_IN,
    //     ]);
    // }

    public function isOutgoing(): bool
    {
        return in_array($this, [
            self::SALE,
            self::DAMAGE,
            self::ADJUSTMENT_OUT,
        ]);
    }



      public function isIncoming(): bool
    {
        return match ($this) {
            self::INITIAL,
            self::PURCHASE,
            self::RETURN,
            self::ADJUSTMENT_IN => true,

            self::SALE,
            self::ADJUSTMENT_OUT,
            self::DAMAGE => false,
        };
    }
}