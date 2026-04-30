<?php

declare(strict_types=1);

namespace App\Enums;

enum RoadworkSeverity: string
{
    case Advisory = 'advisory';
    case Restricted = 'restricted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Advisory => 'Segnalazione',
            self::Restricted => 'Transito limitato',
            self::Closed => 'Tratto chiuso',
        };
    }
}
