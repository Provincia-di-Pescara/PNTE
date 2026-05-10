<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Contracts\OsrmServiceInterface;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RouteSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1', 'app');
    }

    private function makeSystemAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::SystemAdmin->value);

        return $user;
    }

    private function makeCitizen(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Citizen->value);

        return $user;
    }

    public function test_system_admin_can_view_route_simulator(): void
    {
        // Legacy /system/routes redirects to /system/geo/simulator (the live tester).
        $this->actingAs($this->makeSystemAdmin())
            ->get(route('system.routes'))
            ->assertRedirect(route('system.geo.simulator'));

        $this->actingAs($this->makeSystemAdmin())
            ->get(route('system.geo.simulator'))
            ->assertOk();
    }

    public function test_non_system_admin_cannot_access_route_simulator_view(): void
    {
        $this->actingAs($this->makeCitizen())
            ->get(route('system.routes'))
            ->assertForbidden();
    }

    public function test_system_admin_can_call_snap_api(): void
    {
        $this->mock(OsrmServiceInterface::class)
            ->shouldReceive('snapToRoad')
            ->once()
            ->andReturn(['geometry' => 'LINESTRING(13.7 42.1, 13.8 42.2)', 'distance_km' => 12.5]);

        $this->actingAs($this->makeSystemAdmin())
            ->postJson(route('api.routing.snap'), [
                'waypoints' => [['lat' => 42.1, 'lng' => 13.7], ['lat' => 42.2, 'lng' => 13.8]],
            ])
            ->assertOk()
            ->assertJsonStructure(['wkt', 'geojson', 'distance_km']);
    }

    public function test_system_admin_can_call_breakdown_api(): void
    {
        // LINESTRING in the Pacific Ocean — no entities intersect → empty breakdown
        $this->actingAs($this->makeSystemAdmin())
            ->postJson(route('api.routing.breakdown'), [
                'wkt' => 'LINESTRING(-150.0 0.0, -149.9 0.1)',
            ])
            ->assertOk()
            ->assertJson(['breakdown' => [], 'total_km' => 0]);
    }

    public function test_snap_requires_at_least_two_waypoints(): void
    {
        $this->actingAs($this->makeSystemAdmin())
            ->postJson(route('api.routing.snap'), [
                'waypoints' => [['lat' => 42.1, 'lng' => 13.7]],
            ])
            ->assertUnprocessable();
    }

    public function test_breakdown_requires_wkt(): void
    {
        $this->actingAs($this->makeSystemAdmin())
            ->postJson(route('api.routing.breakdown'), [])
            ->assertUnprocessable();
    }

    public function test_ars_overlay_is_blocked_for_system_admin(): void
    {
        $this->actingAs($this->makeSystemAdmin())
            ->postJson(route('api.routing.ars-overlay'), ['wkt' => 'LINESTRING(13.7 42.1, 13.8 42.2)'])
            ->assertForbidden();
    }
}
