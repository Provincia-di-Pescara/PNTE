<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ImportStandardRoutesTest extends TestCase
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

    /** @return array<string, mixed> */
    private function makeGeoJson(array $features): array
    {
        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /** @return array<string, mixed> */
    private function makeFeature(string $nome, array $properties = []): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => [[13.5, 42.5], [13.6, 42.6]],
            ],
            'properties' => array_merge(['nome' => $nome], $properties),
        ];
    }

    public function test_inserts_standard_route_from_geojson(): void
    {
        $entity = Entity::factory()->create();
        $geojson = $this->makeGeoJson([$this->makeFeature('Via Roma')]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'sr_').'.json';
        file_put_contents($tmpFile, json_encode($geojson));

        $this->artisan('gte:import-standard-routes', [
            'file' => $tmpFile,
            'entity_id' => $entity->id,
        ])->assertExitCode(0)
            ->expectsOutputToContain('Inserite: 1');

        $this->assertDatabaseHas('standard_routes', [
            'entity_id' => $entity->id,
            'nome' => 'Via Roma',
        ]);

        $geomSet = DB::selectOne(
            'SELECT geometry IS NOT NULL AS has_geom FROM standard_routes WHERE entity_id = ? AND nome = ?',
            [$entity->id, 'Via Roma']
        );
        $this->assertSame(1, (int) $geomSet->has_geom);

        @unlink($tmpFile);
    }

    public function test_updates_existing_route_on_reimport(): void
    {
        $entity = Entity::factory()->create();
        $geojson = $this->makeGeoJson([$this->makeFeature('Via Roma', ['max_massa_kg' => 40000])]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'sr_').'.json';
        file_put_contents($tmpFile, json_encode($geojson));

        // First import
        $this->artisan('gte:import-standard-routes', ['file' => $tmpFile, 'entity_id' => $entity->id])
            ->assertExitCode(0);

        // Second import with updated limit
        $geojson2 = $this->makeGeoJson([$this->makeFeature('Via Roma', ['max_massa_kg' => 44000])]);
        file_put_contents($tmpFile, json_encode($geojson2));

        $this->artisan('gte:import-standard-routes', ['file' => $tmpFile, 'entity_id' => $entity->id])
            ->assertExitCode(0)
            ->expectsOutputToContain('Aggiornate: 1');

        $this->assertDatabaseHas('standard_routes', [
            'entity_id' => $entity->id,
            'nome' => 'Via Roma',
            'max_massa_kg' => 44000,
        ]);

        @unlink($tmpFile);
    }

    public function test_skips_feature_without_geometry(): void
    {
        $entity = Entity::factory()->create();
        $geojson = $this->makeGeoJson([[
            'type' => 'Feature',
            'geometry' => null,
            'properties' => ['nome' => 'Via Senza Geom'],
        ]]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'sr_').'.json';
        file_put_contents($tmpFile, json_encode($geojson));

        $this->artisan('gte:import-standard-routes', ['file' => $tmpFile, 'entity_id' => $entity->id])
            ->assertExitCode(0)
            ->expectsOutputToContain('Saltate: 1');

        $this->assertDatabaseMissing('standard_routes', ['nome' => 'Via Senza Geom']);

        @unlink($tmpFile);
    }

    public function test_skips_feature_without_nome(): void
    {
        $entity = Entity::factory()->create();
        $feature = [
            'type' => 'Feature',
            'geometry' => ['type' => 'LineString', 'coordinates' => [[13.5, 42.5], [13.6, 42.6]]],
            'properties' => [],
        ];
        $geojson = $this->makeGeoJson([$feature]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'sr_').'.json';
        file_put_contents($tmpFile, json_encode($geojson));

        $this->artisan('gte:import-standard-routes', ['file' => $tmpFile, 'entity_id' => $entity->id])
            ->assertExitCode(0)
            ->expectsOutputToContain('Saltate: 1');

        @unlink($tmpFile);
    }

    public function test_fails_when_file_not_found(): void
    {
        $entity = Entity::factory()->create();

        $this->artisan('gte:import-standard-routes', [
            'file' => '/tmp/nonexistent_file.json',
            'entity_id' => $entity->id,
        ])->assertExitCode(1);
    }

    public function test_fails_when_entity_not_found(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'sr_').'.json';
        file_put_contents($tmpFile, json_encode($this->makeGeoJson([$this->makeFeature('Via Roma')])));

        $this->artisan('gte:import-standard-routes', [
            'file' => $tmpFile,
            'entity_id' => 99999,
        ])->assertExitCode(1);

        @unlink($tmpFile);
    }
}
