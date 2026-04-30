<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OsrmService;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class OsrmServiceTest extends TestCase
{
    private function osrmRouteResponse(string $geojsonCoords, float $distanceM): array
    {
        return [
            'code' => 'Ok',
            'routes' => [[
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => json_decode($geojsonCoords, true),
                ],
                'distance' => $distanceM,
                'duration' => 120.0,
            ]],
        ];
    }

    public function test_snap_to_road_returns_wkt_and_km(): void
    {
        Http::fake([
            '*' => Http::response($this->osrmRouteResponse('[[13.0,42.0],[13.1,42.1]]', 15000.0)),
        ]);

        $service = new OsrmService(app(HttpFactory::class));
        $result = $service->snapToRoad([
            ['lat' => 42.0, 'lng' => 13.0],
            ['lat' => 42.1, 'lng' => 13.1],
        ]);

        $this->assertArrayHasKey('geometry', $result);
        $this->assertArrayHasKey('distance_km', $result);
        $this->assertStringStartsWith('LINESTRING', $result['geometry']);
        $this->assertEqualsWithDelta(15.0, $result['distance_km'], 0.001);
    }

    public function test_alternatives_returns_multiple_routes(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'Ok',
                'routes' => [
                    [
                        'geometry' => ['type' => 'LineString', 'coordinates' => [[13.0, 42.0], [13.1, 42.1]]],
                        'distance' => 15000.0,
                        'duration' => 120.0,
                    ],
                    [
                        'geometry' => ['type' => 'LineString', 'coordinates' => [[13.0, 42.0], [13.05, 42.05], [13.1, 42.1]]],
                        'distance' => 17000.0,
                        'duration' => 140.0,
                    ],
                ],
            ]),
        ]);

        $service = new OsrmService(app(HttpFactory::class));
        $results = $service->alternatives([
            ['lat' => 42.0, 'lng' => 13.0],
            ['lat' => 42.1, 'lng' => 13.1],
        ]);

        $this->assertCount(2, $results);
        $this->assertEqualsWithDelta(15.0, $results[0]['distance_km'], 0.001);
        $this->assertEqualsWithDelta(17.0, $results[1]['distance_km'], 0.001);
    }

    public function test_coordinates_are_sent_as_lng_lat(): void
    {
        Http::fake([
            '*' => Http::response($this->osrmRouteResponse('[[13.5,42.3]]', 5000.0)),
        ]);

        $service = new OsrmService(app(HttpFactory::class));
        $service->snapToRoad([
            ['lat' => 42.3, 'lng' => 13.5],
            ['lat' => 42.4, 'lng' => 13.6],
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '13.5,42.3');
        });
    }
}
