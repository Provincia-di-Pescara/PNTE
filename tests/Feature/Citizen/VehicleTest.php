<?php

declare(strict_types=1);

namespace Tests\Feature\Citizen;

use App\Enums\AxleType;
use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class VehicleTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $operator;
    private User $citizen;
    private User $citizenNoDelegate;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

        $this->operator = User::factory()->create();
        $this->operator->assignRole(UserRole::Operator->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);

        $this->citizenNoDelegate = User::factory()->create();
        $this->citizenNoDelegate->assignRole(UserRole::Citizen->value);

        $this->company = Company::factory()->create();

        // Attach citizen with approved delegation
        $this->company->users()->attach($this->citizen->id, [
            'role'       => 'delegato',
            'valid_from' => today()->toDateString(),
            'valid_to'   => null,
            'approved_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function validVehiclePayload(?int $companyId = null): array
    {
        return [
            'company_id'            => $companyId ?? $this->company->id,
            'tipo'                  => VehicleType::Trattore->value,
            'targa'                 => 'AB123CD',
            'numero_telaio'         => null,
            'marca'                 => 'Volvo',
            'modello'               => 'FH500',
            'anno_immatricolazione' => 2020,
            'massa_vuoto'           => 10000,
            'massa_complessiva'     => 44000,
            'lunghezza'             => 16500,
            'larghezza'             => 2550,
            'altezza'               => 4000,
            'axles'                 => [
                ['posizione' => 1, 'tipo' => AxleType::Singolo->value, 'interasse' => null, 'carico_tecnico' => 8000],
                ['posizione' => 2, 'tipo' => AxleType::Tandem->value, 'interasse' => 1350, 'carico_tecnico' => 16000],
            ],
        ];
    }

    public function test_unauthenticated_redirected(): void
    {
        $this->get(route('my.vehicles.index'))->assertRedirect(route('login'));
        $this->get(route('my.vehicles.create'))->assertRedirect(route('login'));
    }

    public function test_citizen_can_view_vehicles_index(): void
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->citizen)
            ->get(route('my.vehicles.index'))
            ->assertOk()
            ->assertViewIs('citizen.vehicles.index');
    }

    public function test_citizen_cannot_see_vehicles_of_other_companies(): void
    {
        $otherCompany = Company::factory()->create();
        $otherVehicle = Vehicle::factory()->create(['company_id' => $otherCompany->id]);

        // citizen has no delegation to otherCompany — vehicle must not appear in index
        $this->actingAs($this->citizen)
            ->get(route('my.vehicles.index'))
            ->assertOk()
            ->assertDontSee($otherVehicle->targa);
    }

    public function test_operator_can_view_vehicles_index(): void
    {
        $this->actingAs($this->operator)
            ->get(route('my.vehicles.index'))
            ->assertOk();
    }

    public function test_citizen_without_approved_delegation_cannot_access_index(): void
    {
        $this->actingAs($this->citizenNoDelegate)
            ->get(route('my.vehicles.index'))
            ->assertForbidden();
    }

    public function test_citizen_without_approved_delegation_redirected_on_create(): void
    {
        // citizenNoDelegate has no approved delegation — policy denies create
        $this->actingAs($this->citizenNoDelegate)
            ->get(route('my.vehicles.create'))
            ->assertForbidden();
    }

    public function test_citizen_can_access_create_form(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('my.vehicles.create'))
            ->assertOk()
            ->assertViewIs('citizen.vehicles.create');
    }

    public function test_citizen_can_create_vehicle_with_axles(): void
    {
        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('my.vehicles.store'), $this->validVehiclePayload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'company_id'  => $this->company->id,
            'targa'       => 'AB123CD',
            'numero_assi' => 2,
        ]);

        $vehicle = Vehicle::where('targa', 'AB123CD')->firstOrFail();

        $this->assertDatabaseHas('vehicle_axles', [
            'vehicle_id'     => $vehicle->id,
            'posizione'      => 1,
            'tipo'           => AxleType::Singolo->value,
            'carico_tecnico' => 8000,
        ]);

        $this->assertDatabaseHas('vehicle_axles', [
            'vehicle_id'     => $vehicle->id,
            'posizione'      => 2,
            'tipo'           => AxleType::Tandem->value,
            'interasse'      => 1350,
            'carico_tecnico' => 16000,
        ]);

        $this->assertSame(2, $vehicle->axles()->count());
    }

    public function test_store_validates_targa_unique(): void
    {
        Vehicle::factory()->create([
            'company_id' => $this->company->id,
            'targa'      => 'AB123CD',
        ]);

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('my.vehicles.store'), $this->validVehiclePayload())
            ->assertSessionHasErrors(['targa']);
    }

    public function test_store_validates_required_axles(): void
    {
        $payload = $this->validVehiclePayload();
        unset($payload['axles']);

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('my.vehicles.store'), $payload)
            ->assertSessionHasErrors(['axles']);
    }

    public function test_store_validates_axle_carico_tecnico_required(): void
    {
        $payload          = $this->validVehiclePayload();
        $payload['axles'] = [
            ['posizione' => 1, 'tipo' => AxleType::Singolo->value, 'interasse' => null],
        ];

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('my.vehicles.store'), $payload)
            ->assertSessionHasErrors(['axles.0.carico_tecnico']);
    }

    public function test_citizen_can_view_own_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->citizen)
            ->get(route('my.vehicles.show', $vehicle))
            ->assertOk()
            ->assertViewIs('citizen.vehicles.show');
    }

    public function test_citizen_cannot_view_vehicle_of_other_company(): void
    {
        $otherCompany = Company::factory()->create();
        $vehicle      = Vehicle::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->citizen)
            ->get(route('my.vehicles.show', $vehicle))
            ->assertForbidden();
    }

    public function test_citizen_can_update_vehicle_syncing_axles(): void
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $this->company->id]);
        $vehicle->axles()->createMany([
            ['posizione' => 1, 'tipo' => AxleType::Singolo->value, 'carico_tecnico' => 8000],
            ['posizione' => 2, 'tipo' => AxleType::Singolo->value, 'carico_tecnico' => 8000],
            ['posizione' => 3, 'tipo' => AxleType::Singolo->value, 'carico_tecnico' => 8000],
        ]);

        $payload = [
            'tipo'                  => VehicleType::Rimorchio->value,
            'targa'                 => $vehicle->targa,
            'marca'                 => 'Schmitz',
            'modello'               => 'S.CS',
            'anno_immatricolazione' => 2021,
            'massa_vuoto'           => 7000,
            'massa_complessiva'     => 35000,
            'lunghezza'             => 13600,
            'larghezza'             => 2550,
            'altezza'               => 3000,
            'axles'                 => [
                ['posizione' => 1, 'tipo' => AxleType::Tridem->value, 'interasse' => 1310, 'carico_tecnico' => 27000],
            ],
        ];

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('my.vehicles.update', $vehicle), $payload)
            ->assertRedirect(route('my.vehicles.show', $vehicle))
            ->assertSessionHas('success');

        $vehicle->refresh();

        $this->assertSame(1, $vehicle->numero_assi);
        $this->assertSame(1, $vehicle->axles()->count());
        $this->assertDatabaseHas('vehicle_axles', [
            'vehicle_id'     => $vehicle->id,
            'tipo'           => AxleType::Tridem->value,
            'carico_tecnico' => 27000,
        ]);
    }

    public function test_citizen_can_delete_own_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete(route('my.vehicles.destroy', $vehicle))
            ->assertRedirect(route('my.vehicles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_citizen_cannot_delete_vehicle_of_other_company(): void
    {
        $otherCompany = Company::factory()->create();
        $vehicle      = Vehicle::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->citizen)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete(route('my.vehicles.destroy', $vehicle))
            ->assertForbidden();
    }
}
