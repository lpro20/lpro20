<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case MOBILE_MONEY = 'mobile_money';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Espèces',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CARD => 'Carte bancaire',
            self::BANK_TRANSFER => 'Virement bancaire',
        };
    }
}