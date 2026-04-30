<?php

declare(strict_types=1);

namespace App\Enums;

enum RoadworkStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Pianificato',
            self::Active => 'Attivo',
            self::Closed => 'Chiuso',
        };
    }
}
