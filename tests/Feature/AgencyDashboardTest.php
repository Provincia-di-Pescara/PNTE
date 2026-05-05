<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AgencyDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1', 'app');
    }

    private function makeUser(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    public function test_agency_user_can_access_agency_dashboard(): void
    {
        $user = $this->makeUser(UserRole::Agency);

        $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertOk();
    }

    public function test_agency_user_can_access_partners_page(): void
    {
        $user = $this->makeUser(UserRole::Agency);

        $this->actingAs($user)
            ->get(route('agency.partners'))
            ->assertOk();
    }

    public function test_agency_user_can_access_applications_page(): void
    {
        $user = $this->makeUser(UserRole::Agency);

        $this->actingAs($user)
            ->get(route('agency.applications'))
            ->assertOk();
    }

    public function test_agency_user_can_access_audit_page(): void
    {
        $user = $this->makeUser(UserRole::Agency);

        $this->actingAs($user)
            ->get(route('agency.audit'))
            ->assertOk();
    }

    public function test_unauthenticated_redirected_from_agency_dashboard(): void
    {
        $this->get(route('agency.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_citizen_cannot_access_agency_dashboard(): void
    {
        $user = $this->makeUser(UserRole::Citizen);

        $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_ente_cannot_access_agency_dashboard(): void
    {
        $user = $this->makeUser(UserRole::AdminEnte);

        $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertForbidden();
    }

    public function test_root_dashboard_redirects_agency_to_agency_dashboard(): void
    {
        $user = $this->makeUser(UserRole::Agency);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('agency.dashboard'));
    }
}
