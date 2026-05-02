<?php

declare(strict_types=1);

namespace Tests\Feature\ThirdParty;

use App\Enums\ApplicationStatus;
use App\Enums\ClearanceStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Clearance;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ClearanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $thirdParty;

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
    }

    public function test_third_party_can_view_clearances_index(): void
    {
        $this->actingAs($this->thirdParty)
            ->get(route('third-party.clearances.index'))
            ->assertOk();
    }

    public function test_third_party_can_view_clearance_detail(): void
    {
        $application = Application::factory()->create(['stato' => ApplicationStatus::WaitingClearances]);
        $clearance = Clearance::factory()->create([
            'application_id' => $application->id,
            'entity_id' => $this->entity->id,
            'stato' => ClearanceStatus::Pending,
        ]);

        $this->actingAs($this->thirdParty)
            ->get(route('third-party.clearances.show', $clearance))
            ->assertOk();
    }

    public function test_third_party_cannot_view_foreign_clearance(): void
    {
        $foreignEntity = Entity::factory()->create();
        $application = Application::factory()->create();
        $clearance = Clearance::factory()->create([
            'application_id' => $application->id,
            'entity_id' => $foreignEntity->id,
        ]);

        $this->actingAs($this->thirdParty)
            ->get(route('third-party.clearances.show', $clearance))
            ->assertForbidden();
    }

    public function test_third_party_can_approve_clearance(): void
    {
        $application = Application::factory()->create(['stato' => ApplicationStatus::WaitingClearances]);
        $clearance = Clearance::factory()->create([
            'application_id' => $application->id,
            'entity_id' => $this->entity->id,
            'stato' => ClearanceStatus::Pending,
        ]);

        $this->actingAs($this->thirdParty)
            ->post(route('third-party.clearances.approve', $clearance), ['note' => 'OK'])
            ->assertRedirect(route('third-party.clearances.show', $clearance));

        $this->assertDatabaseHas('clearances', [
            'id' => $clearance->id,
            'stato' => ClearanceStatus::Approved->value,
        ]);
    }

    public function test_third_party_can_reject_clearance(): void
    {
        $application = Application::factory()->create(['stato' => ApplicationStatus::WaitingClearances]);
        $clearance = Clearance::factory()->create([
            'application_id' => $application->id,
            'entity_id' => $this->entity->id,
            'stato' => ClearanceStatus::Pending,
        ]);

        $this->actingAs($this->thirdParty)
            ->post(route('third-party.clearances.reject', $clearance), ['note' => 'Diniego per ostacolo viario'])
            ->assertRedirect(route('third-party.clearances.show', $clearance));

        $this->assertDatabaseHas('clearances', [
            'id' => $clearance->id,
            'stato' => ClearanceStatus::Rejected->value,
        ]);
    }
}
