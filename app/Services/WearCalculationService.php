<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tariff;
use App\Models\Vehicle;

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
}
