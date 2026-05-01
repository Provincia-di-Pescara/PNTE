<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use App\Services\StandardRouteOverlayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class StandardRouteOverlayServiceTest extends TestCase
{
    use RefreshDatabase;

    private StandardRouteOverlayService $service;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->service = new StandardRouteOverlayService;
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function createStandardRoute(int $entityId, string $wkt, string $nome = 'SR Test', bool $active = true): int
    {
        DB::statement(
            'INSERT INTO standard_routes (entity_id, nome, geometry, vehicle_types, active, created_at, updated_at)
             VALUES (?, ?, ST_GeomFromText(?, 4326), ?, ?, NOW(), NOW())',
            [$entityId, $nome, $wkt, '[]', $active ? 1 : 0]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    // ── analyze() ────────────────────────────────────────────────────────────

    public function test_analyze_returns_matching_standard_routes(): void
    {
        $entity = Entity::factory()->create();
        $this->createStandardRoute($entity->id, 'LINESTRING(13.5 42.5, 13.6 42.6)');

        $result = $this->service->analyze('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertNotEmpty($result);
        $this->assertSame($entity->id, $result[0]['entity_id']);
        $this->assertSame('SR Test', $result[0]['nome']);
        $this->assertArrayHasKey('geojson', $result[0]);
    }

    public function test_analyze_excludes_inactive_standard_routes(): void
    {
        $entity = Entity::factory()->create();
        $this->createStandardRoute($entity->id, 'LINESTRING(13.5 42.5, 13.6 42.6)', 'SR Inactive', false);

        $result = $this->service->analyze('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertEmpty($result);
    }

    public function test_analyze_returns_empty_when_no_overlap(): void
    {
        $entity = Entity::factory()->create();
        // Standard route far from user route
        $this->createStandardRoute($entity->id, 'LINESTRING(15.0 45.0, 15.1 45.1)');

        $result = $this->service->analyze('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertEmpty($result);
    }

    public function test_analyze_returns_max_limits(): void
    {
        $entity = Entity::factory()->create();
        DB::statement(
            'INSERT INTO standard_routes
                 (entity_id, nome, geometry, vehicle_types, max_massa_kg, max_lunghezza_mm, active, created_at, updated_at)
             VALUES (?, ?, ST_GeomFromText(?, 4326), ?, ?, ?, 1, NOW(), NOW())',
            [$entity->id, 'SR Limits', 'LINESTRING(13.5 42.5, 13.6 42.6)', '[]', 44000, 18750]
        );

        $result = $this->service->analyze('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertSame(44000, $result[0]['max_massa_kg']);
        $this->assertSame(18750, $result[0]['max_lunghezza_mm']);
    }

    // ── segmentCoverage() ────────────────────────────────────────────────────

    public function test_segment_coverage_returns_feature_collection(): void
    {
        $entity = Entity::factory()->create();
        $this->createStandardRoute($entity->id, 'LINESTRING(13.5 42.5, 13.6 42.6)');

        $result = $this->service->segmentCoverage('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertSame('FeatureCollection', $result['type']);
        $this->assertIsArray($result['features']);
        $this->assertNotEmpty($result['features']);
    }

    public function test_segment_coverage_returns_empty_features_when_no_standard_route(): void
    {
        $result = $this->service->segmentCoverage('LINESTRING(13.5 42.5, 13.6 42.6)');

        $this->assertSame('FeatureCollection', $result['type']);
        $this->assertEmpty($result['features']);
    }

    public function test_segment_coverage_coverage_count_one_with_match(): void
    {
        $entity = Entity::factory()->create();
        $this->createStandardRoute($entity->id, 'LINESTRING(13.5 42.5, 13.6 42.6)');

        $result = $this->service->segmentCoverage('LINESTRING(13.5 42.5, 13.6 42.6)');

        $coverageCounts = array_column(
            array_column($result['features'], 'properties'),
            'coverage_count'
        );
        $this->assertContains(1, $coverageCounts);
    }
}
