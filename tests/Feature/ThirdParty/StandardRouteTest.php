<?php

declare(strict_types=1);

namespace Tests\Feature\ThirdParty;

use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class StandardRouteTest extends TestCase
{
    use RefreshDatabase;

    private User $thirdParty;

    private User $operator;

    private User $citizen;

    private Entity $entity;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->entity = Entity::factory()->create();

        $this->thirdParty = User::factory()->create(['entity_id' => $this->entity->id]);
        $this->thirdParty->assignRole(UserRole::ThirdParty->value);

        $this->operator = User::factory()->create();
        $this->operator->assignRole(UserRole::Operator->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    // ── Schema ──────────────────────────────────────────────────────────────

    public function test_standard_route_can_be_inserted_with_geometry(): void
    {
        DB::statement(
            'INSERT INTO standard_routes (entity_id, nome, geometry, vehicle_types, active, created_at, updated_at) VALUES (?, ?, ST_GeomFromText(?, 4326), ?, 1, NOW(), NOW())',
            [$this->entity->id, 'SP17 Pescara–Chieti', 'LINESTRING(13.5 42.3, 13.6 42.4)', json_encode([VehicleType::Trattore->value])]
        );

        $this->assertDatabaseHas('standard_routes', ['nome' => 'SP17 Pescara–Chieti']);
    }

    public function test_standard_route_supports_vehicle_weight_limits(): void
    {
        DB::statement(
            'INSERT INTO standard_routes (entity_id, nome, geometry, vehicle_types, max_massa_kg, max_larghezza_mm, active, created_at, updated_at) VALUES (?, ?, ST_GeomFromText(?, 4326), ?, ?, ?, 1, NOW(), NOW())',
            [$this->entity->id, 'SP1 con limite', 'LINESTRING(13.5 42.3, 13.6 42.4)', json_encode([VehicleType::Trattore->value]), 10_000, 2500]
        );

        $this->assertDatabaseHas('standard_routes', ['nome' => 'SP1 con limite', 'max_massa_kg' => 10_000]);
    }

    // ── Tariff tipo_applicazione ─────────────────────────────────────────────

    public function test_tariff_accepts_tipo_applicazione_analitica_km(): void
    {
        Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'tipo_applicazione' => 'analitica_km',
            'coefficiente' => '0.001000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $this->assertDatabaseHas('tariffs', ['tipo_applicazione' => 'analitica_km']);
    }

    public function test_tariff_accepts_tipo_applicazione_forfettaria_agricola_with_null_tipo_asse(): void
    {
        Tariff::factory()->create([
            'tipo_asse' => null,
            'tipo_applicazione' => 'forfettaria_agricola',
            'coefficiente' => '450.000000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $this->assertDatabaseHas('tariffs', ['tipo_applicazione' => 'forfettaria_agricola', 'tipo_asse' => null]);
    }

    public function test_tariff_scope_by_tipo_returns_correct_records(): void
    {
        Tariff::factory()->create(['tipo_applicazione' => 'analitica_km',          'tipo_asse' => 'singolo']);
        Tariff::factory()->create(['tipo_applicazione' => 'forfettaria_agricola',   'tipo_asse' => null]);
        Tariff::factory()->create(['tipo_applicazione' => 'forfettaria_periodica',  'tipo_asse' => null]);

        $this->assertSame(1, Tariff::byTipoApplicazione('analitica_km')->count());
        $this->assertSame(1, Tariff::byTipoApplicazione('forfettaria_agricola')->count());
        $this->assertSame(1, Tariff::byTipoApplicazione('forfettaria_periodica')->count());
    }

    // ── CRUD RBAC ────────────────────────────────────────────────────────────

    public function test_third_party_can_view_standard_routes(): void
    {
        $this->actingAs($this->thirdParty)
            ->get(route('third-party.standard-routes.index'))
            ->assertOk();
    }

    public function test_third_party_can_create_standard_route(): void
    {
        $response = $this->actingAs($this->thirdParty)
            ->post(route('third-party.standard-routes.store'), [
                'entity_id' => $this->entity->id,
                'nome' => 'SP17 Pescara–Chieti',
                'geometry' => 'LINESTRING(13.5 42.3, 13.6 42.4)',
                'vehicle_types' => [VehicleType::Trattore->value],
                'active' => true,
            ]);

        $response->assertRedirect(route('third-party.standard-routes.index'));
        $this->assertDatabaseHas('standard_routes', ['nome' => 'SP17 Pescara–Chieti']);
    }

    public function test_third_party_cannot_create_standard_route_for_foreign_entity(): void
    {
        $foreignEntity = Entity::factory()->create();

        $response = $this->actingAs($this->thirdParty)
            ->post(route('third-party.standard-routes.store'), [
                'entity_id' => $foreignEntity->id,
                'nome' => 'Strada straniera',
                'geometry' => 'LINESTRING(13.5 42.3, 13.6 42.4)',
                'vehicle_types' => [VehicleType::Trattore->value],
                'active' => true,
            ]);

        $response->assertForbidden();
    }

    public function test_citizen_cannot_access_standard_routes(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('third-party.standard-routes.index'))
            ->assertForbidden();
    }

    public function test_operator_can_view_all_standard_routes(): void
    {
        $this->actingAs($this->operator)
            ->get(route('third-party.standard-routes.index'))
            ->assertOk();
    }
}
