<?php

namespace App\Enums;

enum SaleStatus: string
{
    case DRAFT = 'draft';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
        };
    }
}