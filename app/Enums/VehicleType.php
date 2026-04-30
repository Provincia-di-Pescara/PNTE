<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case Trattore = 'trattore';
    case Rimorchio = 'rimorchio';
    case Semirimorchio = 'semirimorchio';
    case MezzoDopera = 'mezzo_dopera';
    case TratticeAgricola = 'trattrice_agricola';
    case Mietitrebbia = 'mietitrebbia';
    case MacchinaOperatrice = 'macchina_operatrice';
    case RimorchioAgricolo = 'rimorchio_agricolo';

    public function label(): string
    {
        return match ($this) {
            self::Trattore => 'Trattore',
            self::Rimorchio => 'Rimorchio',
            self::Semirimorchio => 'Semirimorchio',
            self::MezzoDopera => "Mezzo d'opera",
            self::TratticeAgricola => 'Trattrice Agricola',
            self::Mietitrebbia => 'Mietitrebbia / Raccoglitrice',
            self::MacchinaOperatrice => 'Macchina Operatrice',
            self::RimorchioAgricolo => 'Rimorchio Agricolo',
        };
    }

    public function isAgricultural(): bool
    {
        return match ($this) {
            self::TratticeAgricola,
            self::Mietitrebbia,
            self::MacchinaOperatrice,
            self::RimorchioAgricolo => true,
            default => false,
        };
    }
}
