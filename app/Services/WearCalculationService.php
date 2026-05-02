<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tariff;
use App\Models\Vehicle;
use App\ValueObjects\WearContext;

/**
 * Calcola l'indennità di usura stradale ai sensi del D.P.R. 495/1992 Allegato I.
 *
 * Formula: Σ_i [ (Q_i / 1000)^4 × L × C_i ]
 *   Q_i = carico tecnico asse i in kg
 *   L   = percorso in km
 *   C_i = coefficiente tariffario per tipo asse i
 */
final class WearCalculationService
{
    /**
     * Calcola l'indennità di usura per un insieme di assi su un dato percorso.
     *
     * @param  array<int, array{tipo: string, carico_tecnico: int}>  $assi
     */
    public function calculate(array $assi, float $km): float
    {
        $total = 0.0;

        foreach ($assi as $asse) {
            $tariff = Tariff::query()
                ->where('tipo_asse', $asse['tipo'])
                ->active()
                ->orderByDesc('valid_from')
                ->firstOrFail();

            $q = $asse['carico_tecnico'] / 1000.0;
            $c = (float) $tariff->coefficiente;

            $total += ($q ** 4) * $km * $c;
        }

        return $total;
    }

    /**
     * Shortcut: calcola l'indennità di usura per un veicolo con i suoi assi.
     */
    public function calculateForVehicle(Vehicle $vehicle, float $km): float
    {
        $vehicle->loadMissing('axles');

        return $this->calculate(
            $vehicle->axles
                ->map(fn ($a) => ['tipo' => $a->tipo->value, 'carico_tecnico' => $a->carico_tecnico])
                ->all(),
            $km
        );
    }

    /**
     * Calcola l'indennità di usura per una domanda di autorizzazione (WearContext).
     *
     * Combina gli assi di trattore + rimorchio (se presente) e applica la formula
     * per ogni ente nel breakdown km × numero viaggi.
     *
     * @return array{
     *   total_eur: float,
     *   breakdown: array<int, array{entity_id: int, km: float, eur: float}>
     * }
     */
    public function calculateForApplication(WearContext $ctx): array
    {
        $ctx->vehicle->loadMissing('axles');
        $ctx->trailer?->loadMissing('axles');

        $tipoApplicazione = $ctx->tipoIstanza->toTipoApplicazione();

        $assi = $ctx->vehicle->axles
            ->map(fn ($a) => ['tipo' => $a->tipo->value, 'carico_tecnico' => $a->carico_tecnico])
            ->all();

        if ($ctx->trailer !== null) {
            $trailerAssi = $ctx->trailer->axles
                ->map(fn ($a) => ['tipo' => $a->tipo->value, 'carico_tecnico' => $a->carico_tecnico])
                ->all();
            $assi = array_merge($assi, $trailerAssi);
        }

        $breakdown = [];
        $totalEur = 0.0;

        foreach ($ctx->entityBreakdownKm as $entityId => $km) {
            $effectiveKm = $km * $ctx->numeroViaggi;
            $eur = $this->calculateWithTipoApplicazione($assi, $effectiveKm, $tipoApplicazione->value);
            $breakdown[] = [
                'entity_id' => $entityId,
                'km' => $km,
                'eur' => $eur,
            ];
            $totalEur += $eur;
        }

        return [
            'total_eur' => $totalEur,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calcola indennità filtrando il tariffario per tipo_applicazione.
     *
     * @param  array<int, array{tipo: string, carico_tecnico: int}>  $assi
     */
    private function calculateWithTipoApplicazione(array $assi, float $km, string $tipoApplicazione): float
    {
        $total = 0.0;

        foreach ($assi as $asse) {
            $tariff = Tariff::query()
                ->where('tipo_asse', $asse['tipo'])
                ->where('tipo_applicazione', $tipoApplicazione)
                ->active()
                ->orderByDesc('valid_from')
                ->first();

            // Fallback to generic active tariff if no tipo_applicazione-specific one exists
            if ($tariff === null) {
                $tariff = Tariff::query()
                    ->where('tipo_asse', $asse['tipo'])
                    ->active()
                    ->orderByDesc('valid_from')
                    ->firstOrFail();
            }

            $q = $asse['carico_tecnico'] / 1000.0;
            $c = (float) $tariff->coefficiente;

            $total += ($q ** 4) * $km * $c;
        }

        return $total;
    }
}
