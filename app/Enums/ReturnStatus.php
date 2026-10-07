<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::COMPLETED => 'Traité',
            self::CANCELLED => 'Annulé',
        };
    }
}