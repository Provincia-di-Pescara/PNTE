<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class DiagnosticsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $systemAdmin;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1');

        $this->systemAdmin = User::factory()->create();
        $this->systemAdmin->assignRole(UserRole::SystemAdmin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    public function test_health_snapshot_returns_json_with_service_array(): void
    {
        $response = $this->actingAs($this->systemAdmin)
            ->getJson(route('system.api.health'));

        $response->assertJsonStructure([
            'ok',
            'checked_at',
            'service_count',
            'services' => [
                'db' => ['service', 'ok', 'latency_ms', 'checked_at'],
            ],
        ]);
    }

    public function test_single_health_returns_diagnostic_result(): void
    {
        $this->actingAs($this->systemAdmin)
            ->getJson(route('system.api.health.single', ['service' => 'db']))
            ->assertJsonStructure([
                'service', 'ok', 'latency_ms', 'checked_at',
            ]);
    }

    public function test_unknown_health_service_returns_404(): void
    {
        $this->actingAs($this->systemAdmin)
            ->getJson(route('system.api.health.single', ['service' => 'bogus']))
            ->assertNotFound();
    }

    public function test_health_api_blocks_non_system_admin(): void
    {
        $this->actingAs($this->citizen)
            ->getJson(route('system.api.health'))
            ->assertForbidden();
    }

    public function test_test_mail_endpoint_uses_mail_facade(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->systemAdmin)
            ->postJson(route('system.api.test.mail'), ['to' => 'test@example.test']);

        $response->assertOk()
            ->assertJsonPath('service', 'mail')
            ->assertJsonPath('ok', true);

        Mail::assertSent(\App\Mail\TestMail::class);

        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'diagnostic.test.mail.sent',
        ]);
    }

    public function test_test_mail_validates_email_format(): void
    {
        $this->actingAs($this->systemAdmin)
            ->postJson(route('system.api.test.mail'), ['to' => 'not-an-email'])
            ->assertUnprocessable();
    }

    public function test_test_geojson_validates_payload(): void
    {
        $payload = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => ['name' => 'Sample'],
                    'geometry' => ['type' => 'LineString', 'coordinates' => [[14.21, 42.46], [13.39, 42.34]]],
                ],
            ],
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('sample.geojson', $payload);

        $this->actingAs($this->systemAdmin)
            ->post(route('system.api.test.geojson'), ['file' => $file], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('feature_count', 1)
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonStructure(['bbox' => ['minLng', 'minLat', 'maxLng', 'maxLat']]);
    }

    public function test_diagnostics_index_renders(): void
    {
        $this->actingAs($this->systemAdmin)
            ->get(route('system.diagnostics.index'))
            ->assertOk()
            ->assertSee('Health globale dei servizi')
            ->assertSee('Esegui diagnostica completa');
    }
}
