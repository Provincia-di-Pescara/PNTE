<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case Trattore = 'trattore';
    case Rimorchio = 'rimorchio';
    case Semirimorchio = 'semirimorchio';
    case MezzoDopera = 'mezzo_dopera';

    public function label(): string
    {
        return match ($this) {
            self::Trattore => 'Trattore',
            self::Rimorchio => 'Rimorchio',
            self::Semirimorchio => 'Semirimorchio',
            self::MezzoDopera => "Mezzo d'opera",
        };
    }
}
