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

final class ImportGeoDataTest extends TestCase
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

    public function test_imports_geom_for_matching_entity(): void
    {
        $entity = Entity::factory()->create(['codice_istat' => '068001']);

        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['cod_istat' => '068001'],
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => [[[[13.0, 42.0], [13.1, 42.0], [13.1, 42.1], [13.0, 42.1], [13.0, 42.0]]]],
                ],
            ]],
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'geo_').'.json';
        file_put_contents($tmpFile, $geojson);

        $this->artisan('gte:import-geo', ['file' => $tmpFile])
            ->assertExitCode(0);

        $result = DB::selectOne('SELECT geom IS NOT NULL AS has_geom FROM entities WHERE id = ?', [$entity->id]);
        $this->assertSame(1, (int) $result->has_geom);

        @unlink($tmpFile);
    }

    public function test_warns_when_entity_not_found(): void
    {
        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['cod_istat' => '999999'],
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => [[[[13.0, 42.0], [13.1, 42.0], [13.1, 42.1], [13.0, 42.1], [13.0, 42.0]]]],
                ],
            ]],
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'geo_').'.json';
        file_put_contents($tmpFile, $geojson);

        $this->artisan('gte:import-geo', ['file' => $tmpFile])
            ->assertExitCode(0)
            ->expectsOutputToContain('Non trovate: 1');

        @unlink($tmpFile);
    }

    public function test_fails_when_file_not_found(): void
    {
        $this->artisan('gte:import-geo', ['file' => '/nonexistent/path.json'])
            ->assertExitCode(1);
    }
}
