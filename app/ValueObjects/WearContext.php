<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\TipoIstanza;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * Immutable value object carrying all context needed by WearCalculationService::calculateForApplication().
 */
final class WearContext
{
    /**
     * @param  array<int, float>  $entityBreakdownKm  entity_id => km for that entity's stretch
     */
    public function __construct(
        public readonly TipoIstanza $tipoIstanza,
        public readonly Vehicle $vehicle,
        public readonly ?Vehicle $trailer,
        public readonly array $entityBreakdownKm,
        public readonly float $totalKm,
        public readonly int $numeroViaggi,
        public readonly Carbon $validaDa,
        public readonly Carbon $validaFino,
    ) {}

    public static function forAnalitica(
        Vehicle $vehicle,
        ?Vehicle $trailer,
        array $entityBreakdownKm,
        float $totalKm,
        Carbon $validaDa,
        Carbon $validaFino,
    ): self {
        return new self(
            tipoIstanza: TipoIstanza::AnaliticaKm,
            vehicle: $vehicle,
            trailer: $trailer,
            entityBreakdownKm: $entityBreakdownKm,
            totalKm: $totalKm,
            numeroViaggi: 1,
            validaDa: $validaDa,
            validaFino: $validaFino,
        );
    }

    public static function forForfettariaPeriodica(
        Vehicle $vehicle,
        ?Vehicle $trailer,
        array $entityBreakdownKm,
        float $totalKm,
        int $numeroViaggi,
        Carbon $validaDa,
        Carbon $validaFino,
    ): self {
        return new self(
            tipoIstanza: TipoIstanza::ForfettariaPeriodica,
            vehicle: $vehicle,
            trailer: $trailer,
            entityBreakdownKm: $entityBreakdownKm,
            totalKm: $totalKm,
            numeroViaggi: $numeroViaggi,
            validaDa: $validaDa,
            validaFino: $validaFino,
        );
    }

    public static function forForfettariaAgricola(
        Vehicle $vehicle,
        array $entityBreakdownKm,
        float $totalKm,
        Carbon $validaDa,
        Carbon $validaFino,
    ): self {
        return new self(
            tipoIstanza: TipoIstanza::ForfettariaAgricola,
            vehicle: $vehicle,
            trailer: null,
            entityBreakdownKm: $entityBreakdownKm,
            totalKm: $totalKm,
            numeroViaggi: 1,
            validaDa: $validaDa,
            validaFino: $validaFino,
        );
    }

    /** Effective km to calculate wear on (total × numero viaggi). */
    public function effectiveKm(): float
    {
        return $this->totalKm * $this->numeroViaggi;
    }
}
