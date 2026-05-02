<?php

declare(strict_types=1);

namespace App\Enums;

enum TripStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'In corso',
            self::Completed => 'Completato',
            self::Cancelled => 'Annullato',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Completed => 'blue',
            self::Cancelled => 'gray',
        };
    }
}
