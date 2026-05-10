<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\FetchGeoBoundariesJob;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FetchGeoBoundariesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1', 'app');
        Cache::flush();
    }

    public function test_job_sets_cache_to_completed_on_success(): void
    {
        $user = User::factory()->create(['name' => 'Test Admin']);

        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['cod_prov' => '099', 'name' => 'Provincia Test'],
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => [[[[13.0, 42.0], [13.1, 42.0], [13.1, 42.1], [13.0, 42.1], [13.0, 42.0]]]],
                ],
            ]],
        ]);

        Http::fake(['*' => Http::response($geojson, 200)]);

        $job = new FetchGeoBoundariesJob('province', 'https://example.com/province.geojson', $user->id, $user->name);
        $job->handle();

        $status = Cache::get('geo_import_status');

        $this->assertNotNull($status);
        $this->assertSame('completed', $status['status']);
        $this->assertSame('province', $status['tipo']);
        $this->assertIsArray($status['result']);
    }

    public function test_job_sets_cache_to_failed_on_http_error(): void
    {
        $user = User::factory()->create();

        Http::fake(['*' => Http::response('', 503)]);

        $job = new FetchGeoBoundariesJob('comuni', 'https://example.com/comuni.geojson', $user->id, $user->name);
        $job->handle();

        $status = Cache::get('geo_import_status');

        $this->assertNotNull($status);
        $this->assertSame('failed', $status['status']);
        $this->assertNotEmpty($status['error']);
    }

    public function test_job_creates_audit_log_on_success(): void
    {
        $user = User::factory()->create(['name' => 'Operatore Audit']);

        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['cod_prov' => '088', 'name' => 'Provincia Audit'],
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => [[[[13.0, 42.0], [13.1, 42.0], [13.1, 42.1], [13.0, 42.1], [13.0, 42.0]]]],
                ],
            ]],
        ]);

        Http::fake(['*' => Http::response($geojson, 200)]);

        $job = new FetchGeoBoundariesJob('province', 'https://example.com/province.geojson', $user->id, $user->name);
        $job->handle();

        $this->assertDatabaseHas('system_audit_logs', [
            'actor_id' => $user->id,
            'actor_name' => 'Operatore Audit',
            'action' => 'geo.fetch-istat',
        ]);
    }

    public function test_job_creates_audit_log_on_failure(): void
    {
        $user = User::factory()->create(['name' => 'Admin Fail']);

        Http::fake(['*' => Http::response('', 500)]);

        $job = new FetchGeoBoundariesJob('comuni', 'https://example.com/bad.geojson', $user->id, $user->name);
        $job->handle();

        $this->assertDatabaseHas('system_audit_logs', [
            'actor_id' => $user->id,
            'actor_name' => 'Admin Fail',
            'action' => 'geo.fetch-istat',
        ]);
    }

    public function test_failed_callback_sets_cache_and_logs(): void
    {
        $user = User::factory()->create(['name' => 'Admin']);

        $job = new FetchGeoBoundariesJob('comuni', 'https://example.com/x.geojson', $user->id, $user->name);
        $job->failed(new \RuntimeException('Job timeout'));

        $status = Cache::get('geo_import_status');
        $this->assertSame('failed', $status['status']);
        $this->assertStringContainsString('Job timeout', $status['error']);

        $this->assertDatabaseHas('system_audit_logs', ['action' => 'geo.fetch-istat']);
    }
}
