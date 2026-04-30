<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Route;
use App\Models\Setting;
use App\Services\RouteIntersectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RouteIntersectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');
    }

    public function test_returns_km_greater_than_zero_for_overlapping_entity(): void
    {
        $entity = Entity::factory()->create();
        DB::statement(
            "UPDATE entities SET geom = ST_GeomFromText('MULTIPOLYGON(((13.0 42.0, 14.0 42.0, 14.0 43.0, 13.0 43.0, 13.0 42.0)))', 4326) WHERE id = ?",
            [$entity->id]
        );

        $route = Route::factory()->create();
        DB::statement(
            "UPDATE routes SET geometry = ST_GeomFromText('LINESTRING(13.5 42.5, 13.6 42.6)', 4326) WHERE id = ?",
            [$route->id]
        );

        $service = new RouteIntersectionService;
        $breakdown = $service->breakdown($route);

        $this->assertArrayHasKey($entity->id, $breakdown);
        $this->assertGreaterThan(0, $breakdown[$entity->id]);
    }

    public function test_returns_km_less_than_200_for_abruzzo_sized_route(): void
    {
        $entity = Entity::factory()->create();
        DB::statement(
            "UPDATE entities SET geom = ST_GeomFromText('MULTIPOLYGON(((13.0 42.0, 14.0 42.0, 14.0 43.0, 13.0 43.0, 13.0 42.0)))', 4326) WHERE id = ?",
            [$entity->id]
        );

        $route = Route::factory()->create();
        DB::statement(
            "UPDATE routes SET geometry = ST_GeomFromText('LINESTRING(13.5 42.5, 13.6 42.6)', 4326) WHERE id = ?",
            [$route->id]
        );

        $service = new RouteIntersectionService;
        $breakdown = $service->breakdown($route);

        $this->assertArrayHasKey($entity->id, $breakdown);
        $this->assertLessThan(200, $breakdown[$entity->id]);
    }

    public function test_ignores_entities_with_null_geom(): void
    {
        $entityWithGeom = Entity::factory()->create();
        DB::statement(
            "UPDATE entities SET geom = ST_GeomFromText('MULTIPOLYGON(((13.0 42.0, 14.0 42.0, 14.0 43.0, 13.0 43.0, 13.0 42.0)))', 4326) WHERE id = ?",
            [$entityWithGeom->id]
        );

        /** @var Entity $entityWithoutGeom */
        $entityWithoutGeom = Entity::factory()->create();

        $route = Route::factory()->create();
        DB::statement(
            "UPDATE routes SET geometry = ST_GeomFromText('LINESTRING(13.5 42.5, 13.6 42.6)', 4326) WHERE id = ?",
            [$route->id]
        );

        $service = new RouteIntersectionService;
        $breakdown = $service->breakdown($route);

        $this->assertArrayNotHasKey($entityWithoutGeom->id, $breakdown);
        $this->assertArrayHasKey($entityWithGeom->id, $breakdown);
    }
}
