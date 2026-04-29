<?php

declare(strict_types=1);

namespace Tests\Feature\ThirdParty;

use App\Enums\RoadworkSeverity;
use App\Enums\RoadworkStatus;
use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Roadwork;
use App\Models\Route as RouteModel;
use App\Models\Setting;
use App\Models\User;
use App\Services\RoadworkConflictService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RoadworkTest extends TestCase
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

    private function validPayload(?int $entityId = null): array
    {
        return [
            'entity_id' => $entityId ?? $this->entity->id,
            'title' => 'Cantiere test',
            'geometry' => 'LINESTRING(13.5 42.3, 13.6 42.4)',
            'valid_from' => '2026-05-01',
            'valid_to' => '2026-08-31',
            'severity' => RoadworkSeverity::Advisory->value,
            'status' => RoadworkStatus::Planned->value,
            'note' => null,
        ];
    }

    public function test_third_party_can_view_roadworks(): void
    {
        $this->actingAs($this->thirdParty)
            ->get(route('third-party.roadworks.index'))
            ->assertOk();
    }

    public function test_third_party_can_create_roadwork(): void
    {
        $response = $this->actingAs($this->thirdParty)
            ->post(route('third-party.roadworks.store'), $this->validPayload());

        $response->assertRedirect(route('third-party.roadworks.index'));
        $this->assertDatabaseHas('roadworks', ['title' => 'Cantiere test', 'entity_id' => $this->entity->id]);
    }

    public function test_third_party_cannot_create_roadwork_for_foreign_entity(): void
    {
        $foreignEntity = Entity::factory()->create();

        $response = $this->actingAs($this->thirdParty)
            ->post(route('third-party.roadworks.store'), $this->validPayload($foreignEntity->id));

        $response->assertForbidden();
    }

    public function test_operator_can_manage_any_roadwork(): void
    {
        $roadwork = Roadwork::factory()->create(['entity_id' => $this->entity->id]);
        DB::statement('UPDATE roadworks SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?', ['LINESTRING(13.5 42.3, 13.6 42.4)', $roadwork->id]);

        $this->actingAs($this->operator)
            ->get(route('third-party.roadworks.index'))
            ->assertOk();

        $this->actingAs($this->operator)
            ->get(route('third-party.roadworks.edit', $roadwork))
            ->assertOk();
    }

    public function test_citizen_cannot_access_roadworks(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('third-party.roadworks.create'))
            ->assertForbidden();
    }

    public function test_conflict_service_detects_active_roadwork_on_route(): void
    {
        $route = RouteModel::factory()->create(['user_id' => $this->thirdParty->id]);
        DB::statement('UPDATE routes SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $route->id]);

        $roadwork = Roadwork::factory()->create([
            'entity_id' => $this->entity->id,
            'status' => RoadworkStatus::Active->value,
            'valid_from' => '2026-04-01',
            'valid_to' => '2026-12-31',
        ]);
        DB::statement('UPDATE roadworks SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $roadwork->id]);

        $conflicts = app(RoadworkConflictService::class)
            ->conflicts($route, Carbon::parse('2026-06-01'));

        $this->assertCount(1, $conflicts);
        $this->assertTrue($conflicts->first()->is($roadwork));
    }

    public function test_conflict_service_ignores_closed_roadwork(): void
    {
        $route = RouteModel::factory()->create(['user_id' => $this->thirdParty->id]);
        DB::statement('UPDATE routes SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $route->id]);

        $roadwork = Roadwork::factory()->create([
            'entity_id' => $this->entity->id,
            'status' => RoadworkStatus::Closed->value,
            'valid_from' => '2026-04-01',
            'valid_to' => '2026-12-31',
        ]);
        DB::statement('UPDATE roadworks SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $roadwork->id]);

        $conflicts = app(RoadworkConflictService::class)
            ->conflicts($route, Carbon::parse('2026-06-01'));

        $this->assertCount(0, $conflicts);
    }

    public function test_conflict_service_ignores_future_roadwork(): void
    {
        $route = RouteModel::factory()->create(['user_id' => $this->thirdParty->id]);
        DB::statement('UPDATE routes SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $route->id]);

        $roadwork = Roadwork::factory()->create([
            'entity_id' => $this->entity->id,
            'status' => RoadworkStatus::Planned->value,
            'valid_from' => '2027-01-01',
            'valid_to' => '2027-12-31',
        ]);
        DB::statement('UPDATE roadworks SET geometry = ST_GeomFromText(?, 4326) WHERE id = ?',
            ['LINESTRING(13.5 42.3, 13.6 42.4)', $roadwork->id]);

        $conflicts = app(RoadworkConflictService::class)
            ->conflicts($route, Carbon::parse('2026-06-01'));

        $this->assertCount(0, $conflicts);
    }
}
