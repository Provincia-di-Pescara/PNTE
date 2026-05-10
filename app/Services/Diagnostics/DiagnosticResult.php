<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use Carbon\CarbonImmutable;

final readonly class DiagnosticResult
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public string $service,
        public bool $ok,
        public int $latencyMs,
        public ?string $version,
        public array $details,
        public ?string $error,
        public CarbonImmutable $checkedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $details
     */
    public static function ok(
        string $service,
        int $latencyMs,
        ?string $version = null,
        array $details = [],
        ?CarbonImmutable $checkedAt = null,
    ): self {
        return new self(
            service: $service,
            ok: true,
            latencyMs: $latencyMs,
            version: $version,
            details: $details,
            error: null,
            checkedAt: $checkedAt ?? CarbonImmutable::now(),
        );
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function fail(
        string $service,
        string $error,
        int $latencyMs = 0,
        array $details = [],
        ?CarbonImmutable $checkedAt = null,
    ): self {
        return new self(
            service: $service,
            ok: false,
            latencyMs: $latencyMs,
            version: null,
            details: $details,
            error: $error,
            checkedAt: $checkedAt ?? CarbonImmutable::now(),
        );
    }

    /**
     * @return array{service: string, ok: bool, latency_ms: int, version: ?string, details: array<string, mixed>, error: ?string, checked_at: string}
     */
    public function toArray(): array
    {
        return [
            'service' => $this->service,
            'ok' => $this->ok,
            'latency_ms' => $this->latencyMs,
            'version' => $this->version,
            'details' => $this->details,
            'error' => $this->error,
            'checked_at' => $this->checkedAt->toIso8601String(),
        ];
    }
}
