<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\SystemAuditLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IntegrationsTest extends TestCase
{
    use RefreshDatabase;

    private User $systemAdmin;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        Setting::set('setup_completed', '1');

        $this->systemAdmin = User::factory()->create();
        $this->systemAdmin->assignRole(UserRole::SystemAdmin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    public function test_system_admin_sees_integrations_index(): void
    {
        $this->actingAs($this->systemAdmin)
            ->get(route('system.integrations.index'))
            ->assertOk()
            ->assertSee('SPID/CIE OIDC')
            ->assertSee('PDND')
            ->assertSee('PagoPA')
            ->assertSee('SMTP outbound')
            ->assertSee('PEC / IMAP listener')
            ->assertSee('AINOP');
    }

    public function test_citizen_cannot_access_integrations(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('system.integrations.index'))
            ->assertForbidden();
    }

    public function test_show_renders_each_service_with_three_tabs(): void
    {
        foreach (['oidc', 'pdnd', 'pagopa', 'smtp', 'pec', 'ainop'] as $service) {
            $this->actingAs($this->systemAdmin)
                ->get(route('system.integrations.show', ['service' => $service]))
                ->assertOk()
                ->assertSee('Configurazione')
                ->assertSee('Test connessione')
                ->assertSee('Audit');
        }
    }

    public function test_unknown_service_returns_404(): void
    {
        $this->actingAs($this->systemAdmin)
            ->get(route('system.integrations.show', ['service' => 'bogus']))
            ->assertNotFound();
    }

    public function test_update_persists_settings_and_logs_audit(): void
    {
        $this->actingAs($this->systemAdmin)
            ->put(route('system.integrations.update', ['service' => 'smtp']), [
                'mail_host' => 'mail.example.test',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_username' => 'noreply@example.test',
                'mail_from_address' => 'noreply@example.test',
                'mail_from_name' => 'PNTE Test',
                'mail_password' => 'segreta',
            ])
            ->assertRedirect(route('system.integrations.show', ['service' => 'smtp']))
            ->assertSessionHas('success');

        $this->assertSame('mail.example.test', Setting::get('mail_host'));
        $this->assertSame('587', Setting::get('mail_port'));
        $this->assertSame('segreta', Setting::get('mail_password'));

        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'integration.smtp.updated',
        ]);
    }

    public function test_update_does_not_overwrite_secret_when_left_blank(): void
    {
        Setting::set('mail_password', 'pwd-original', 'mail');

        $this->actingAs($this->systemAdmin)
            ->put(route('system.integrations.update', ['service' => 'smtp']), [
                'mail_host' => 'mail.example.test',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_username' => 'u',
                'mail_from_address' => 'u@example.test',
                'mail_from_name' => 'PNTE',
                'mail_password' => '',
            ])
            ->assertRedirect();

        $this->assertSame('pwd-original', Setting::get('mail_password'));
    }

    public function test_test_endpoint_runs_diagnostic_and_logs(): void
    {
        $this->actingAs($this->systemAdmin)
            ->post(route('system.integrations.test', ['service' => 'oidc']))
            ->assertRedirect();

        $this->assertTrue(
            SystemAuditLog::query()
                ->where('action', 'diagnostic.run.oidc')
                ->exists()
        );
    }
}
