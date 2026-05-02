<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoIstanza: string
{
    case AnaliticaKm = 'analitica_km';
    case ForfettariaAgricola = 'forfettaria_agricola';
    case ForfettariaPeriodica = 'forfettaria_periodica';

    public function label(): string
    {
        return match ($this) {
            self::AnaliticaKm => 'Analitica per km (singolo viaggio)',
            self::ForfettariaAgricola => 'Forfettaria Agricola (uso agricolo)',
            self::ForfettariaPeriodica => 'Forfettaria Periodica (multiviaggio su itinerario fisso)',
        };
    }

    public function isFlat(): bool
    {
        return match ($this) {
            self::AnaliticaKm => false,
            self::ForfettariaAgricola, self::ForfettariaPeriodica => true,
        };
    }

    public function requiresNumeroViaggi(): bool
    {
        return $this === self::ForfettariaPeriodica;
    }

    public function requiresSelectedEntities(): bool
    {
        return $this === self::ForfettariaPeriodica;
    }

    /** Returns the corresponding TipoApplicazioneTariff for wear calculation. */
    public function toTipoApplicazione(): TipoApplicazioneTariff
    {
        return match ($this) {
            self::AnaliticaKm => TipoApplicazioneTariff::AnaliticaKm,
            self::ForfettariaAgricola => TipoApplicazioneTariff::ForfettariaAgricola,
            self::ForfettariaPeriodica => TipoApplicazioneTariff::ForfettariaPeriodica,
        };
    }
}
