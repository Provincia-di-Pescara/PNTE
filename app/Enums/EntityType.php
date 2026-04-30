<?php

declare(strict_types=1);

namespace App\Enums;

enum EntityType: string
{
    case Comune = 'comune';
    case Provincia = 'provincia';
    case Anas = 'anas';
    case Autostrada = 'autostrada';
    case Regione = 'regione';

    public function label(): string
    {
        return match ($this) {
            self::Comune => 'Comune',
            self::Provincia => 'Provincia',
            self::Anas => 'ANAS',
            self::Autostrada => 'Autostrada',
            self::Regione => 'Regione',
        };
    }
}
