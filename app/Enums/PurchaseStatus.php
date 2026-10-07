<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case DRAFT = 'draft';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::RECEIVED => 'Réceptionné',
            self::CANCELLED => 'Annulé',
        };
    }
}