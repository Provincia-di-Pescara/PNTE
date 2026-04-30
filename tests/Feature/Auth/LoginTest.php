<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Simulate completed setup so middleware passes
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.it',
            'password' => 'password-sicura-123',
        ]);
        $user->assignRole(UserRole::SuperAdmin->value);

        $this->post(route('login.post'), [
            'email' => 'admin@test.it',
            'password' => 'password-sicura-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_password_fails(): void
    {
        User::factory()->create([
            'email' => 'admin@test.it',
            'password' => 'password-sicura-123',
        ]);

        $this->post(route('login.post'), [
            'email' => 'admin@test.it',
            'password' => 'sbagliata',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_with_unknown_email_fails(): void
    {
        $this->post(route('login.post'), [
            'email' => 'inesistente@test.it',
            'password' => 'qualsiasi',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_validates_required_fields(): void
    {
        $this->post(route('login.post'), [])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_logout_clears_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
