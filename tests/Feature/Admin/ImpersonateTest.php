<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\ImpersonationLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    private User $systemAdmin;

    private User $citizen;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');

        $this->systemAdmin = User::factory()->create();
        $this->systemAdmin->assignRole(UserRole::SystemAdmin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);

        $this->operator = User::factory()->create();
        $this->operator->assignRole(UserRole::Operator->value);
    }

    public function test_system_admin_can_impersonate_citizen(): void
    {
        $this->actingAs($this->systemAdmin)
            ->post(route('system.users.impersonate', $this->citizen))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('info')
            ->assertSessionHas('impersonated_by', $this->systemAdmin->id);
    }

    public function test_impersonation_creates_log_entry(): void
    {
        $this->actingAs($this->systemAdmin)
            ->post(route('system.users.impersonate', $this->citizen));

        $this->assertDatabaseHas('impersonation_logs', [
            'impersonator_id' => $this->systemAdmin->id,
            'impersonated_id' => $this->citizen->id,
        ]);

        $log = ImpersonationLog::first();
        $this->assertNotNull($log->started_at);
        $this->assertNull($log->ended_at);
    }

    public function test_leaving_impersonation_closes_log(): void
    {
        $this->actingAs($this->systemAdmin)
            ->post(route('system.users.impersonate', $this->citizen));

        $this->actingAs($this->citizen)
            ->withSession(['impersonated_by' => $this->systemAdmin->id])
            ->delete(route('impersonate.leave'));

        $log = ImpersonationLog::first();
        $this->assertNotNull($log->ended_at);
    }

    public function test_operator_cannot_impersonate(): void
    {
        $this->actingAs($this->operator)
            ->post(route('system.users.impersonate', $this->citizen))
            ->assertForbidden();
    }

    public function test_system_admin_cannot_be_impersonated(): void
    {
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->assignRole(UserRole::SystemAdmin->value);

        $this->actingAs($this->systemAdmin)
            ->post(route('system.users.impersonate', $anotherAdmin))
            ->assertForbidden();
    }

    public function test_citizen_cannot_impersonate(): void
    {
        $this->actingAs($this->citizen)
            ->post(route('system.users.impersonate', $this->operator))
            ->assertForbidden();
    }
}
