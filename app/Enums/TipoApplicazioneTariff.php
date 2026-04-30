<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoApplicazioneTariff: string
{
    case AnaliticaKm = 'analitica_km';
    case ForfettariaAgricola = 'forfettaria_agricola';
    case ForfettariaPeriodica = 'forfettaria_periodica';

    public function label(): string
    {
        return match ($this) {
            self::AnaliticaKm => 'Analitica per km (D.P.R. 495/1992)',
            self::ForfettariaAgricola => 'Forfettaria Agricola (Art. 104 CdS)',
            self::ForfettariaPeriodica => 'Forfettaria Periodica (Art. 10 CdS)',
        };
    }

    public function isFlat(): bool
    {
        return $this !== self::AnaliticaKm;
    }
}
