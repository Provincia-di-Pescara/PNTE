<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClearanceDispatchServiceInterface;
use App\Enums\ApplicationStatus;
use App\Enums\ClearanceStatus;
use App\Jobs\SendClearanceNotification;
use App\Models\Application;
use App\Models\Clearance;

final class ClearanceDispatchService implements ClearanceDispatchServiceInterface
{
    public function __construct(
        private readonly StandardRouteOverlayService $arsOverlay,
    ) {}

    public function dispatch(Application $application): void
    {
        $application->loadMissing(['route', 'vehicle', 'trailer']);

        $entityBreakdown = $application->route?->entity_breakdown ?? [];
        $entityIds = array_keys($entityBreakdown);

        if (empty($entityIds)) {
            $this->transitionToPayment($application);

            return;
        }

        $arsRoutes = $this->getArsRoutesByEntity($application);
        $allPreCleared = true;

        foreach ($entityIds as $entityId) {
            $entityId = (int) $entityId;
            $isPreCleared = $this->isPreCleared($application, $entityId, $arsRoutes);

            $clearance = Clearance::create([
                'application_id' => $application->id,
                'entity_id' => $entityId,
                'stato' => $isPreCleared ? ClearanceStatus::PreCleared : ClearanceStatus::Pending,
            ]);

            if (! $isPreCleared) {
                $allPreCleared = false;
                SendClearanceNotification::dispatch($clearance);
            }
        }

        if ($allPreCleared) {
            $this->transitionToPayment($application);
        } else {
            $application->stato = ApplicationStatus::WaitingClearances;
            $application->save();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $arsRoutes  keyed by entity_id
     */
    private function isPreCleared(Application $application, int $entityId, array $arsRoutes): bool
    {
        if (! isset($arsRoutes[$entityId])) {
            return false;
        }

        $vehicle = $application->vehicle;
        $trailer = $application->trailer;

        $massaKg = (int) (($vehicle->massa_complessiva ?? 0) + ($trailer?->massa_complessiva ?? 0));
        $lunghezzaMm = (int) (($vehicle->lunghezza ?? 0) + ($trailer?->lunghezza ?? 0));
        $larghezzaMm = (int) max($vehicle->larghezza ?? 0, $trailer?->larghezza ?? 0);
        $altezzaMm = (int) max($vehicle->altezza ?? 0, $trailer?->altezza ?? 0);

        foreach ($arsRoutes[$entityId] as $arsRoute) {
            if ($arsRoute['max_massa_kg'] !== null && $massaKg > $arsRoute['max_massa_kg']) {
                continue;
            }
            if ($arsRoute['max_lunghezza_mm'] !== null && $lunghezzaMm > $arsRoute['max_lunghezza_mm']) {
                continue;
            }
            if ($arsRoute['max_larghezza_mm'] !== null && $larghezzaMm > $arsRoute['max_larghezza_mm']) {
                continue;
            }
            if ($arsRoute['max_altezza_mm'] !== null && $altezzaMm > $arsRoute['max_altezza_mm']) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns ARS routes grouped by entity_id from the route's WKT.
     *
     * @return array<int, list<array<string, mixed>>>
     */
    private function getArsRoutesByEntity(Application $application): array
    {
        $route = $application->route;

        if ($route === null) {
            return [];
        }

        try {
            $routeWkt = $route->getRawGeometry();
        } catch (\RuntimeException) {
            return [];
        }

        $matched = $this->arsOverlay->analyze($routeWkt);
        $byEntity = [];

        foreach ($matched as $arsRoute) {
            $byEntity[$arsRoute['entity_id']][] = $arsRoute;
        }

        return $byEntity;
    }

    private function transitionToPayment(Application $application): void
    {
        $application->stato = ApplicationStatus::WaitingPayment;
        $application->save();
    }
}
