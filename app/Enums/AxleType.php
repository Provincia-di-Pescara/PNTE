<?php

declare(strict_types=1);

namespace App\Enums;

enum AxleType: string
{
    case Singolo = 'singolo';
    case Tandem = 'tandem';
    case Tridem = 'tridem';

    public function label(): string
    {
        return match ($this) {
            self::Singolo => 'Singolo',
            self::Tandem => 'Tandem',
            self::Tridem => 'Tridem',
        };
    }
}
