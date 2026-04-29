<?php

declare(strict_types=1);

namespace Tests\Feature\Citizen;

use App\Contracts\OsrmServiceInterface;
use App\Enums\UserRole;
use App\Models\Route;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RouteBuilderTest extends TestCase
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

    public function test_citizen_can_view_route_builder(): void
    {
        $response = $this->actingAs($this->citizen)->get(route('my.routes.create'));
        $response->assertOk();
        $response->assertSee('map');
    }

    public function test_unauthenticated_cannot_access_route_builder(): void
    {
        $response = $this->get(route('my.routes.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_store_saves_route_with_geometry(): void
    {
        $wkt = 'LINESTRING(13.0 42.0, 13.1 42.1)';
        $response = $this->actingAs($this->citizen)->post(route('my.routes.store'), [
            'waypoints' => json_encode([['lat' => 42.0, 'lng' => 13.0], ['lat' => 42.1, 'lng' => 13.1]]),
            'geometry' => $wkt,
            'distance_km' => 15.0,
        ]);
        $route = Route::query()->where('user_id', $this->citizen->id)->sole();
        $response->assertRedirect(route('my.routes.show', $route));
        $this->assertDatabaseHas('routes', ['user_id' => $this->citizen->id, 'distance_km' => 15.0]);
    }

    public function test_snap_api_returns_geojson(): void
    {
        $this->mock(OsrmServiceInterface::class)
            ->shouldReceive('snapToRoad')
            ->once()
            ->andReturn(['geometry' => 'LINESTRING(13.0 42.0, 13.1 42.1)', 'distance_km' => 15.0]);

        $response = $this->actingAs($this->citizen)->postJson(route('api.routing.snap'), [
            'waypoints' => [['lat' => 42.0, 'lng' => 13.0], ['lat' => 42.1, 'lng' => 13.1]],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['wkt', 'geojson', 'distance_km']);
    }
}
