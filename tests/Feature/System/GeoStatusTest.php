<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Console\Commands\FetchIstatBoundaries;
use App\Enums\UserRole;
use App\Jobs\FetchGeoBoundariesJob;
use App\Jobs\ImportGeoFileJob;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class GeoStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1', 'app');
        Cache::flush();
    }

    private function makeSystemAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::SystemAdmin->value);

        return $user;
    }

    public function test_status_returns_idle_when_no_cache(): void
    {
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->getJson(route('system.geo.status'))
            ->assertOk()
            ->assertJsonFragment(['status' => 'idle']);
    }

    public function test_status_returns_cached_state(): void
    {
        $user = $this->makeSystemAdmin();

        Cache::put('geo_import_status', [
            'status' => 'downloading',
            'tipo' => 'comuni',
            'step' => 'Test step',
        ], 3600);

        $this->actingAs($user)
            ->getJson(route('system.geo.status'))
            ->assertOk()
            ->assertJsonFragment(['status' => 'downloading', 'tipo' => 'comuni']);
    }

    public function test_status_requires_system_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Citizen->value);

        $this->actingAs($user)
            ->getJson(route('system.geo.status'))
            ->assertForbidden();
    }

    public function test_fetch_geo_dispatches_job_for_province(): void
    {
        Queue::fake();
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->postJson(route('system.geo.fetch'), ['tipo' => 'province'])
            ->assertOk()
            ->assertJsonFragment(['ok' => true]);

        Queue::assertPushed(FetchGeoBoundariesJob::class, function (FetchGeoBoundariesJob $job): bool {
            return $job->tipo === 'province';
        });
    }

    public function test_fetch_geo_dispatches_two_jobs_for_tutti(): void
    {
        Queue::fake();
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->postJson(route('system.geo.fetch'), ['tipo' => 'tutti'])
            ->assertOk();

        Queue::assertPushed(FetchGeoBoundariesJob::class, 2);
    }

    public function test_fetch_geo_uses_custom_source_url(): void
    {
        Queue::fake();
        Setting::set('geo.source_province_url', 'https://custom.example.com/province.geojson', 'geo');

        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->postJson(route('system.geo.fetch'), ['tipo' => 'province'])
            ->assertOk();

        Queue::assertPushed(FetchGeoBoundariesJob::class, function (FetchGeoBoundariesJob $job): bool {
            return $job->sourceUrl === 'https://custom.example.com/province.geojson';
        });
    }

    public function test_fetch_geo_requires_system_admin(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $user->assignRole(UserRole::Citizen->value);

        $this->actingAs($user)
            ->postJson(route('system.geo.fetch'), ['tipo' => 'province'])
            ->assertForbidden();

        Queue::assertNotPushed(FetchGeoBoundariesJob::class);
    }

    public function test_import_geo_dispatches_file_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = $this->makeSystemAdmin();

        $geojson = json_encode(['type' => 'FeatureCollection', 'features' => []]);
        $file = UploadedFile::fake()->createWithContent('test.geojson', $geojson);

        $this->actingAs($user)
            ->post(route('system.geo.import'), ['file' => $file])
            ->assertOk()
            ->assertJsonFragment(['ok' => true]);

        Queue::assertPushed(ImportGeoFileJob::class, function (ImportGeoFileJob $job): bool {
            return $job->originalName === 'test.geojson';
        });
    }

    public function test_save_sources_persists_to_settings(): void
    {
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->put(route('system.geo.sources'), [
                'source_comuni_url' => 'https://mirror.example.com/comuni.geojson',
                'source_province_url' => 'https://mirror.example.com/province.geojson',
            ])
            ->assertRedirect(route('system.geo'));

        $this->assertSame('https://mirror.example.com/comuni.geojson', Setting::get('geo.source_comuni_url'));
        $this->assertSame('https://mirror.example.com/province.geojson', Setting::get('geo.source_province_url'));
    }

    public function test_save_sources_rejects_invalid_url(): void
    {
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->put(route('system.geo.sources'), [
                'source_comuni_url' => 'not-a-url',
                'source_province_url' => 'https://ok.example.com/x.geojson',
            ])
            ->assertSessionHasErrors('source_comuni_url');
    }

    public function test_geo_dashboard_shows_correct_counts(): void
    {
        $user = $this->makeSystemAdmin();

        $this->actingAs($user)
            ->get(route('system.geo'))
            ->assertOk()
            ->assertViewHas('comuniCount')
            ->assertViewHas('provinceCount')
            ->assertViewHas('importStatus')
            ->assertViewHas('geoSources');
    }

    public function test_geo_dashboard_sources_show_defaults(): void
    {
        $user = $this->makeSystemAdmin();

        $response = $this->actingAs($user)->get(route('system.geo'));
        $sources = $response->viewData('geoSources');

        $this->assertSame(FetchIstatBoundaries::DEFAULT_URLS['comuni'], $sources['comuni_url']);
        $this->assertSame(FetchIstatBoundaries::DEFAULT_URLS['province'], $sources['province_url']);
    }
}
