<?php

declare(strict_types=1);

namespace Tests\Feature\Citizen;

use App\Contracts\ApplicationServiceInterface;
use App\Enums\ApplicationStatus;
use App\Enums\TipoIstanza;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Route;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    private Company $company;

    private Vehicle $vehicle;

    private Route $route;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);

        $this->company = Company::factory()->create();
        $this->citizen->companies()->attach($this->company->id, [
            'role' => 'owner',
            'valid_from' => today()->toDateString(),
            'valid_to' => null,
            'approved_at' => now(),
        ]);

        $this->vehicle = Vehicle::factory()->create(['company_id' => $this->company->id]);
        $this->route = Route::factory()->create(['user_id' => $this->citizen->id]);
    }

    public function test_citizen_can_view_applications_index(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('my.applications.index'))
            ->assertOk();
    }

    public function test_unauthenticated_redirected_from_applications(): void
    {
        $this->get(route('my.applications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_citizen_can_view_create_form(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('my.applications.create'))
            ->assertOk();
    }

    public function test_citizen_can_store_application(): void
    {
        $this->mock(ApplicationServiceInterface::class, function ($mock) {
            $mock->shouldReceive('submit')->once();
        });

        $response = $this->actingAs($this->citizen)
            ->post(route('my.applications.store'), [
                'tipo_istanza' => TipoIstanza::AnaliticaKm->value,
                'company_id' => $this->company->id,
                'vehicle_id' => $this->vehicle->id,
                'route_id' => $this->route->id,
                'valida_da' => now()->addDay()->format('Y-m-d'),
                'valida_fino' => now()->addMonth()->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->citizen->id,
            'company_id' => $this->company->id,
            'vehicle_id' => $this->vehicle->id,
        ]);
    }

    public function test_citizen_can_view_own_application(): void
    {
        $application = Application::factory()->create([
            'user_id' => $this->citizen->id,
            'company_id' => $this->company->id,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $this->actingAs($this->citizen)
            ->get(route('my.applications.show', $application))
            ->assertOk();
    }

    public function test_citizen_cannot_view_foreign_application(): void
    {
        $other = User::factory()->create();
        $other->assignRole(UserRole::Citizen->value);

        $application = Application::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->citizen)
            ->get(route('my.applications.show', $application))
            ->assertForbidden();
    }

    public function test_citizen_can_edit_own_draft(): void
    {
        $application = Application::factory()->create([
            'user_id' => $this->citizen->id,
            'stato' => ApplicationStatus::Draft,
        ]);

        $this->actingAs($this->citizen)
            ->get(route('my.applications.edit', $application))
            ->assertOk();
    }

    public function test_citizen_cannot_edit_submitted_application(): void
    {
        $application = Application::factory()->create([
            'user_id' => $this->citizen->id,
            'stato' => ApplicationStatus::Submitted,
        ]);

        $this->actingAs($this->citizen)
            ->get(route('my.applications.edit', $application))
            ->assertForbidden();
    }
}
