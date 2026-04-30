<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    public function test_super_admin_can_list_users(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.users.index'))
            ->assertOk()
            ->assertViewIs('admin.settings.users.index');
    }

    public function test_super_admin_can_view_user_detail(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.users.show', $this->citizen))
            ->assertOk()
            ->assertViewIs('admin.settings.users.show');
    }

    public function test_super_admin_can_update_user_role(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('admin.settings.users.role', $this->citizen), [
                'role' => UserRole::Operator->value,
            ])
            ->assertRedirect(route('admin.settings.users.show', $this->citizen))
            ->assertSessionHas('success');

        $this->assertTrue($this->citizen->fresh()->hasRole(UserRole::Operator->value));
    }

    public function test_cannot_change_role_of_super_admin(): void
    {
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.settings.users.role', $anotherAdmin), [
                'role' => UserRole::Citizen->value,
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_assign_entity_to_user(): void
    {
        $entity = Entity::factory()->create();

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.settings.users.entity', $this->citizen), [
                'entity_id' => $entity->id,
            ])
            ->assertRedirect(route('admin.settings.users.show', $this->citizen))
            ->assertSessionHas('success');

        $this->assertSame($entity->id, $this->citizen->fresh()->entity_id);
    }

    public function test_operator_cannot_access_user_management(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole(UserRole::Operator->value);

        $this->actingAs($operator)
            ->get(route('admin.settings.users.index'))
            ->assertForbidden();
    }
}
