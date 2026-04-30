<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SettingMailTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $operator;

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
    }

    public function test_super_admin_can_view_mail_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.mail'))
            ->assertOk()
            ->assertViewIs('admin.settings.mail');
    }

    public function test_operator_cannot_access_settings(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.settings.mail'))
            ->assertForbidden();
    }

    public function test_super_admin_can_update_mail_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.mail.update'), [
                'mail_host' => 'smtp.example.com',
                'mail_port' => '587',
                'mail_encryption' => 'tls',
                'mail_username' => 'user@example.com',
                'mail_password' => 'secret123',
                'mail_from_address' => 'noreply@example.com',
                'mail_from_name' => 'GTE Abruzzo',
            ])
            ->assertRedirect(route('admin.settings.mail'))
            ->assertSessionHas('success');

        $this->assertSame('smtp.example.com', Setting::get('mail_host'));
        $this->assertSame('587', Setting::get('mail_port'));
        $this->assertSame('tls', Setting::get('mail_encryption'));
        $this->assertSame('noreply@example.com', Setting::get('mail_from_address'));
    }

    public function test_password_not_overwritten_when_blank(): void
    {
        Setting::set('mail_password', 'existing-secret', 'mail');

        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.mail.update'), [
                'mail_host' => 'smtp.example.com',
                'mail_port' => '587',
                'mail_encryption' => 'tls',
                'mail_username' => 'user@example.com',
                'mail_password' => '',
                'mail_from_address' => 'noreply@example.com',
                'mail_from_name' => 'GTE Abruzzo',
            ])
            ->assertRedirect(route('admin.settings.mail'));

        $this->assertSame('existing-secret', Setting::get('mail_password'));
    }

    public function test_super_admin_can_send_test_email(): void
    {
        Mail::fake();

        Setting::set('mail_host', 'smtp.example.com', 'mail');

        $this->actingAs($this->superAdmin)
            ->post(route('admin.settings.mail.test'))
            ->assertRedirect(route('admin.settings.mail'))
            ->assertSessionHas('success');

        Mail::assertSent(TestMail::class);
    }

    public function test_test_mail_fails_gracefully_with_invalid_config(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.settings.mail.test'))
            ->assertRedirect(route('admin.settings.mail'))
            ->assertSessionHas('error');
    }
}
