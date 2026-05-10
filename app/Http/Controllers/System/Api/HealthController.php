<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TestMailRequest;
use App\Http\Requests\System\TestRoutingRequest;
use App\Mail\TestMail;
use App\Models\SystemAuditLog;
use App\Services\Diagnostics\DiagnosticResult;
use App\Services\Diagnostics\HealthCheckService;
use App\Services\OsrmService;
use App\Services\RouteIntersectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class HealthController extends Controller
{
    public function __construct(
        private readonly HealthCheckService $health,
        private readonly OsrmService $osrm,
        private readonly RouteIntersectionService $intersection,
    ) {}

    public function all(): JsonResponse
    {
        $results = $this->health->runAll();
        $summary = $this->health->summarize($results);

        return response()->json($summary, $summary['ok'] ? 200 : 503);
    }

    public function single(string $service): JsonResponse
    {
        try {
            $result = $this->health->runOne($service);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->json($result->toArray(), $result->ok ? 200 : 503);
    }

    public function testMail(TestMailRequest $request): JsonResponse
    {
        $start = hrtime(true);
        $to = (string) $request->validated('to');
        $actor = $request->user();

        try {
            Mail::to($to)->send(new TestMail);
            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

            SystemAuditLog::query()->create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name ?? 'sistema',
                'action' => 'diagnostic.test.mail.sent',
                'detail' => 'Test mail inviato a '.$to.' ('.$latencyMs.' ms)',
                'created_at' => now(),
            ]);

            return response()->json(
                DiagnosticResult::ok(
                    service: 'mail',
                    latencyMs: $latencyMs,
                    version: 'mailer: '.config('mail.default'),
                    details: ['to' => $to],
                )->toArray()
            );
        } catch (Throwable $e) {
            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

            SystemAuditLog::query()->create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name ?? 'sistema',
                'action' => 'diagnostic.test.mail.failed',
                'detail' => 'Errore: '.$e->getMessage(),
                'created_at' => now(),
            ]);

            return response()->json(
                DiagnosticResult::fail(
                    service: 'mail',
                    error: $e->getMessage(),
                    latencyMs: $latencyMs,
                    details: ['to' => $to],
                )->toArray(),
                503
            );
        }
    }

    public function testRouting(TestRoutingRequest $request): JsonResponse
    {
        $start = hrtime(true);

        try {
            $waypoints = [
                ['lat' => (float) $request->validated('from.lat'), 'lng' => (float) $request->validated('from.lng')],
                ['lat' => (float) $request->validated('to.lat'),   'lng' => (float) $request->validated('to.lng')],
            ];

            $snap = $this->osrm->snapToRoad($waypoints);
            $breakdown = $this->intersection->breakdownFromWkt((string) $snap['geometry']);

            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

            return response()->json([
                'ok' => true,
                'latency_ms' => $latencyMs,
                'distance_km' => $snap['distance_km'],
                'wkt' => $snap['geometry'],
                'entities_intersected' => count($breakdown),
                'breakdown' => $breakdown,
                'checked_at' => now()->toImmutable()->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'latency_ms' => (int) round((hrtime(true) - $start) / 1_000_000),
                'error' => $e->getMessage(),
                'checked_at' => now()->toImmutable()->toIso8601String(),
            ], 503);
        }
    }

    public function testGeojson(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:application/json,application/geo+json,text/plain'],
        ]);

        $start = hrtime(true);
        $file = $request->file('file');
        $contents = (string) file_get_contents($file->getRealPath());

        try {
            $payload = json_decode($contents, true, 64, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'JSON non valido: '.$e->getMessage(),
                'checked_at' => now()->toImmutable()->toIso8601String(),
            ], 422);
        }

        if (! is_array($payload) || ! isset($payload['type'])) {
            return response()->json([
                'ok' => false,
                'error' => 'Documento non è un oggetto GeoJSON (manca `type`).',
                'checked_at' => now()->toImmutable()->toIso8601String(),
            ], 422);
        }

        $type = (string) $payload['type'];
        $features = $type === 'FeatureCollection' ? (array) ($payload['features'] ?? []) : [$payload];
        $bbox = $this->bbox($features);

        $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

        return response()->json([
            'ok' => true,
            'latency_ms' => $latencyMs,
            'type' => $type,
            'feature_count' => count($features),
            'bbox' => $bbox,
            'sample_properties' => $this->sampleProperties($features),
            'size_bytes' => mb_strlen($contents, '8bit'),
            'checked_at' => now()->toImmutable()->toIso8601String(),
        ]);
    }

    /**
     * @param  array<int, mixed>  $features
     * @return array{minLng: float, minLat: float, maxLng: float, maxLat: float}|null
     */
    private function bbox(array $features): ?array
    {
        $minLng = INF;
        $minLat = INF;
        $maxLng = -INF;
        $maxLat = -INF;
        $found = false;

        foreach ($features as $f) {
            if (! is_array($f)) {
                continue;
            }
            $geom = $f['geometry'] ?? $f;
            $coords = $geom['coordinates'] ?? null;
            if ($coords === null) {
                continue;
            }
            $this->visitCoords($coords, function (float $lng, float $lat) use (&$minLng, &$minLat, &$maxLng, &$maxLat, &$found): void {
                $minLng = min($minLng, $lng);
                $minLat = min($minLat, $lat);
                $maxLng = max($maxLng, $lng);
                $maxLat = max($maxLat, $lat);
                $found = true;
            });
        }

        if (! $found) {
            return null;
        }

        return [
            'minLng' => round($minLng, 6),
            'minLat' => round($minLat, 6),
            'maxLng' => round($maxLng, 6),
            'maxLat' => round($maxLat, 6),
        ];
    }

    /** @param  mixed  $coords */
    private function visitCoords($coords, callable $visit): void
    {
        if (! is_array($coords) || $coords === []) {
            return;
        }
        if (is_numeric($coords[0]) && isset($coords[1]) && is_numeric($coords[1])) {
            $visit((float) $coords[0], (float) $coords[1]);

            return;
        }
        foreach ($coords as $sub) {
            $this->visitCoords($sub, $visit);
        }
    }

    /**
     * @param  array<int, mixed>  $features
     * @return array<string, mixed>
     */
    private function sampleProperties(array $features): array
    {
        foreach ($features as $f) {
            if (is_array($f) && ! empty($f['properties']) && is_array($f['properties'])) {
                return array_slice($f['properties'], 0, 6, true);
            }
        }

        return [];
    }
}
