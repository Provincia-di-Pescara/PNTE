<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SettingsTest extends TestCase
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

    public function test_settings_index_accessible_by_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertViewIs('admin.settings.index');
    }

    public function test_settings_index_forbidden_for_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_general_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.general'))
            ->assertOk()
            ->assertViewIs('admin.settings.general');
    }

    public function test_super_admin_can_update_general_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.general.update'), [
                'app_name' => 'GTE Molise',
                'app_timezone' => 'Europe/Rome',
                'app_locale' => 'it',
            ])
            ->assertRedirect(route('admin.settings.general'))
            ->assertSessionHas('success');

        $this->assertSame('GTE Molise', Setting::get('app_name'));
        $this->assertSame('Europe/Rome', Setting::get('app_timezone'));
        $this->assertSame('it', Setting::get('app_locale'));
    }

    public function test_general_settings_validates_timezone(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.general.update'), [
                'app_name' => 'Test',
                'app_timezone' => 'Invalid/Zone',
                'app_locale' => 'it',
            ])
            ->assertSessionHasErrors('app_timezone');
    }

    public function test_super_admin_can_view_branding_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.branding'))
            ->assertOk()
            ->assertViewIs('admin.settings.branding');
    }

    public function test_super_admin_can_update_branding(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.branding.update'), [
                'brand_header_title' => 'GTE Campania',
                'brand_primary_color' => '#FF5500',
            ])
            ->assertRedirect(route('admin.settings.branding'))
            ->assertSessionHas('success');

        $this->assertSame('GTE Campania', Setting::get('brand_header_title'));
        $this->assertSame('#FF5500', Setting::get('brand_primary_color'));
    }
}
