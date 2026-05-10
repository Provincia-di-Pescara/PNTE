<?php

declare(strict_types=1);

namespace App\Services\Diagnostics\Contracts;

use App\Services\Diagnostics\DiagnosticResult;

interface DiagnosticInterface
{
    /**
     * Stable identifier for the service (e.g. "osrm", "postgis"). Used in API
     * paths, artisan signatures, and audit log actions.
     */
    public function key(): string;

    /**
     * Human label shown in UI (e.g. "OSRM routing engine").
     */
    public function label(): string;

    /**
     * Execute the check and return its result. Implementations MUST never
     * throw — failures are encoded in DiagnosticResult::fail().
     */
    public function run(): DiagnosticResult;
}
