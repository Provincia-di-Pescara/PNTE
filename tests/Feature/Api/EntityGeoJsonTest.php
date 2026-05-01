<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\EntityType;
use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EntityGeoJsonTest extends TestCase
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

    private function setEntityGeom(int $entityId): void
    {
        DB::statement(
            "UPDATE entities SET geom = ST_GeomFromText('MULTIPOLYGON(((13.0 42.0, 14.0 42.0, 14.0 43.0, 13.0 43.0, 13.0 42.0)))', 4326) WHERE id = ?",
            [$entityId]
        );
    }

    public function test_returns_geojson_feature_collection(): void
    {
        $entity = Entity::factory()->create(['tipo' => EntityType::Comune]);
        $this->setEntityGeom($entity->id);

        $response = $this->getJson('/api/entities/geojson');

        $response->assertOk();
        $response->assertJsonPath('type', 'FeatureCollection');
        $response->assertJsonStructure([
            'type',
            'features' => [
                ['type', 'geometry', 'properties' => ['id', 'nome', 'tipo', 'codice_istat']],
            ],
        ]);
    }

    public function test_excludes_entities_without_geom(): void
    {
        Entity::factory()->create(['tipo' => EntityType::Comune]); // geom = null

        $response = $this->getJson('/api/entities/geojson');

        $response->assertOk();
        $this->assertEmpty($response->json('features'));
    }

    public function test_filters_by_tipo(): void
    {
        $comune = Entity::factory()->create(['tipo' => EntityType::Comune]);
        $provincia = Entity::factory()->create(['tipo' => EntityType::Provincia]);

        $this->setEntityGeom($comune->id);
        $this->setEntityGeom($provincia->id);

        $response = $this->getJson('/api/entities/geojson?tipo=comune');

        $response->assertOk();
        $features = $response->json('features');
        $this->assertCount(1, $features);
        $this->assertSame('comune', $features[0]['properties']['tipo']);
    }

    public function test_returns_all_entities_without_tipo_filter(): void
    {
        $comune = Entity::factory()->create(['tipo' => EntityType::Comune]);
        $provincia = Entity::factory()->create(['tipo' => EntityType::Provincia]);

        $this->setEntityGeom($comune->id);
        $this->setEntityGeom($provincia->id);

        $response = $this->getJson('/api/entities/geojson');

        $response->assertOk();
        $this->assertCount(2, $response->json('features'));
    }

    public function test_invalid_tipo_returns_unprocessable(): void
    {
        $response = $this->getJson('/api/entities/geojson?tipo=invalid_type');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tipo']);
    }

    public function test_accessible_without_authentication(): void
    {
        $response = $this->getJson('/api/entities/geojson');

        $response->assertOk();
    }
}
