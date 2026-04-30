<?php

declare(strict_types=1);

namespace Tests\Feature\Setup;

use App\Enums\UserRole;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_index_redirects_to_step1_when_not_complete(): void
    {
        $this->get(route('setup.index'))
            ->assertRedirect(route('setup.step1'));
    }

    public function test_setup_index_redirects_to_dashboard_when_complete(): void
    {
        $this->seedRoles();
        Setting::set('setup_completed', '1');

        $user = User::factory()->create();
        $user->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($user)
            ->get(route('setup.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_step1_is_accessible(): void
    {
        $this->get(route('setup.step1'))
            ->assertOk()
            ->assertViewIs('setup.step1');
    }

    public function test_step1_validates_required_fields(): void
    {
        $this->post(route('setup.step1.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_step1_validates_password_confirmation(): void
    {
        $this->post(route('setup.step1.store'), [
            'name' => 'Admin',
            'email' => 'admin@test.it',
            'password' => 'password-sicura-123',
            'password_confirmation' => 'diversa',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_step1_validates_minimum_password_length(): void
    {
        $this->post(route('setup.step1.store'), [
            'name' => 'Admin',
            'email' => 'admin@test.it',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_step1_stores_in_session_and_redirects_to_step2(): void
    {
        $this->post(route('setup.step1.store'), [
            'name' => 'Mario Rossi',
            'email' => 'mario@provincia.pe.it',
            'password' => 'password-sicura-123',
            'password_confirmation' => 'password-sicura-123',
        ])->assertRedirect(route('setup.step2'))
            ->assertSessionHas('setup.admin.email', 'mario@provincia.pe.it');
    }

    public function test_step2_is_accessible(): void
    {
        $this->get(route('setup.step2'))
            ->assertOk()
            ->assertViewIs('setup.step2');
    }

    public function test_step2_validates_required_fields(): void
    {
        $this->post(route('setup.step2.store'), [])
            ->assertSessionHasErrors(['app_name', 'app_timezone', 'app_locale']);
    }

    public function test_step2_rejects_invalid_timezone(): void
    {
        $this->post(route('setup.step2.store'), [
            'app_name' => 'GTE Test',
            'app_timezone' => 'Mars/Olympus',
            'app_locale' => 'it',
        ])->assertSessionHasErrors(['app_timezone']);
    }

    public function test_step2_stores_in_session_and_redirects_to_step3(): void
    {
        $this->post(route('setup.step2.store'), [
            'app_name' => 'GTE Abruzzo Test',
            'app_timezone' => 'Europe/Rome',
            'app_locale' => 'it',
        ])->assertRedirect(route('setup.step3'))
            ->assertSessionHas('setup.app.app_name', 'GTE Abruzzo Test');
    }

    public function test_step3_is_accessible(): void
    {
        $this->get(route('setup.step3'))
            ->assertOk()
            ->assertViewIs('setup.step3');
    }

    public function test_step3_can_be_skipped_without_mail_host(): void
    {
        $this->post(route('setup.step3.store'), [])
            ->assertRedirect(route('setup.riepilogo'))
            ->assertSessionHasNoErrors();
    }

    public function test_step3_validates_mail_fields_when_host_provided(): void
    {
        $this->post(route('setup.step3.store'), [
            'mail_host' => 'smtp.example.com',
            // missing other required fields
        ])->assertSessionHasErrors(['mail_port', 'mail_encryption', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name']);
    }

    public function test_riepilogo_redirects_to_step1_without_session_data(): void
    {
        $this->get(route('setup.riepilogo'))
            ->assertRedirect(route('setup.step1'));
    }

    public function test_riepilogo_is_accessible_with_session_data(): void
    {
        $this->withSession([
            'setup.admin' => ['name' => 'Admin', 'email' => 'admin@test.it', 'password' => 'pass'],
            'setup.app' => ['app_name' => 'GTE', 'app_timezone' => 'Europe/Rome', 'app_locale' => 'it'],
        ])->get(route('setup.riepilogo'))
            ->assertOk()
            ->assertViewIs('setup.riepilogo');
    }

    public function test_complete_creates_admin_user_roles_and_settings(): void
    {
        $this->withSession([
            'setup.admin' => [
                'name' => 'Mario Rossi',
                'email' => 'mario@provincia.pe.it',
                'password' => 'password-sicura-123',
            ],
            'setup.app' => [
                'app_name' => 'GTE Abruzzo',
                'app_timezone' => 'Europe/Rome',
                'app_locale' => 'it',
            ],
        ])->post(route('setup.complete'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('setup_complete', true);

        // Roles created
        foreach (UserRole::cases() as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role->value]);
        }

        // Admin user created with correct role
        $user = User::where('email', 'mario@provincia.pe.it')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(UserRole::SuperAdmin->value));

        // Settings persisted
        $this->assertDatabaseHas('settings', ['key' => 'setup_completed', 'value' => '1']);
        $this->assertDatabaseHas('settings', ['key' => 'app_name', 'value' => 'GTE Abruzzo']);
        $this->assertDatabaseHas('settings', ['key' => 'app_timezone', 'value' => 'Europe/Rome']);

        // Session cleared
        $this->assertNull(session('setup.admin'));
    }

    public function test_middleware_redirects_to_setup_when_not_complete(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('setup.index'));
    }

    public function test_middleware_allows_access_when_setup_complete(): void
    {
        $this->seedRoles();
        Setting::set('setup_completed', '1');

        $user = User::factory()->create();
        $user->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_can_send_test_email_during_setup(): void
    {
        Mail::fake();

        $this->withSession([
            'setup.admin' => ['name' => 'Admin', 'email' => 'admin@test.it', 'password' => 'pass'],
        ])->post(route('setup.test-email'), [
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_username' => 'user@example.com',
            'mail_password' => 'secret',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'GTE Abruzzo',
        ])->assertRedirect(route('setup.step3'))
            ->assertSessionHas('success');

        Mail::assertSent(TestMail::class);
    }

    public function test_test_email_fails_without_mail_config(): void
    {
        $this->withSession([
            'setup.admin' => ['name' => 'Admin', 'email' => 'admin@test.it', 'password' => 'pass'],
        ])->post(route('setup.test-email'), [])
            ->assertRedirect(route('setup.step3'))
            ->assertSessionHas('error');
    }

    private function seedRoles(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }
}
