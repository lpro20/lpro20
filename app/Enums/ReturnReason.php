<?php

namespace App\Enums;

enum ReturnReason: string
{
    case WRONG_SIZE = 'wrong_size';
    case DEFECTIVE = 'defective';
    case WRONG_PRODUCT = 'wrong_product';
    case CUSTOMER_CHANGE = 'customer_change';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WRONG_SIZE => 'Mauvaise taille',
            self::DEFECTIVE => 'Produit défectueux',
            self::WRONG_PRODUCT => 'Mauvais produit',
            self::CUSTOMER_CHANGE => 'Changement d’avis',
            self::OTHER => 'Autre',
        };
    }
}