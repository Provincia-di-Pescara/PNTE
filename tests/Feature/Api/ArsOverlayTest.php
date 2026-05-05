<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ArsOverlayTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    private function createStandardRoute(int $entityId, string $wkt, string $nome = 'SR Test'): void
    {
        DB::statement(
            'INSERT INTO standard_routes (entity_id, nome, geometry, vehicle_types, active, created_at, updated_at)
             VALUES (?, ?, ST_GeomFromText(?, 4326), ?, true, NOW(), NOW())',
            [$entityId, $nome, $wkt, '[]']
        );
    }

    public function test_unauthenticated_cannot_call_ars_overlay(): void
    {
        $response = $this->postJson('/api/routing/ars-overlay', [
            'wkt' => 'LINESTRING(13.5 42.5, 13.6 42.6)',
        ]);

        $response->assertUnauthorized();
    }

    public function test_returns_json_with_matched_routes_and_coverage_geojson(): void
    {
        $entity = Entity::factory()->create();
        $this->createStandardRoute($entity->id, 'LINESTRING(13.5 42.5, 13.6 42.6)');

        $response = $this->actingAs($this->citizen)->postJson('/api/routing/ars-overlay', [
            'wkt' => 'LINESTRING(13.5 42.5, 13.6 42.6)',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'matched_routes' => [['id', 'nome', 'entity_id', 'vehicle_types', 'geojson']],
            'coverage_geojson' => ['type', 'features'],
        ]);
        $this->assertSame('FeatureCollection', $response->json('coverage_geojson.type'));
    }

    public function test_returns_empty_matched_routes_when_no_standard_route_overlaps(): void
    {
        $response = $this->actingAs($this->citizen)->postJson('/api/routing/ars-overlay', [
            'wkt' => 'LINESTRING(13.5 42.5, 13.6 42.6)',
        ]);

        $response->assertOk();
        $this->assertEmpty($response->json('matched_routes'));
        $this->assertSame('FeatureCollection', $response->json('coverage_geojson.type'));
    }

    public function test_wkt_is_required(): void
    {
        $response = $this->actingAs($this->citizen)->postJson('/api/routing/ars-overlay', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['wkt']);
    }
}
