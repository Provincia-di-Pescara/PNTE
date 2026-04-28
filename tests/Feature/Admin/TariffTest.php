<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class TariffTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $operator;

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

        $this->operator = User::factory()->create();
        $this->operator->assignRole(UserRole::Operator->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    public function test_super_admin_can_view_tariffs(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.tariffs.index'))
            ->assertOk()
            ->assertViewIs('admin.tariffs.index');
    }

    public function test_operator_can_view_tariffs(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.tariffs.index'))
            ->assertOk();
    }

    public function test_citizen_cannot_access_tariffs(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('admin.tariffs.index'))
            ->assertForbidden();
    }

    public function test_operator_can_create_tariff(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.tariffs.store'), [
                'tipo_asse' => 'singolo',
                'coefficiente' => '0.001000',
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'note' => 'Tariffa test',
            ])->assertRedirect(route('admin.tariffs.index'));

        $this->assertDatabaseHas('tariffs', [
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.001000',
        ]);
    }

    public function test_store_validates_tipo_asse_enum(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.tariffs.store'), [
                'tipo_asse' => 'invalido',
                'coefficiente' => '0.001000',
                'valid_from' => '2024-01-01',
            ])->assertSessionHasErrors(['tipo_asse']);
    }

    public function test_operator_can_update_tariff(): void
    {
        $tariff = Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.001000',
            'valid_from' => '2024-01-01',
            'valid_to' => null,
        ]);

        $this->actingAs($this->operator)
            ->put(route('admin.tariffs.update', $tariff), [
                'tipo_asse' => 'tandem',
                'coefficiente' => '0.002000',
                'valid_from' => '2024-06-01',
                'valid_to' => null,
            ])->assertRedirect(route('admin.tariffs.index'));

        $this->assertDatabaseHas('tariffs', [
            'id' => $tariff->id,
            'tipo_asse' => 'tandem',
            'coefficiente' => '0.002000',
        ]);
    }

    public function test_operator_can_delete_tariff(): void
    {
        $tariff = Tariff::factory()->create();

        $this->actingAs($this->operator)
            ->delete(route('admin.tariffs.destroy', $tariff))
            ->assertRedirect(route('admin.tariffs.index'));

        $this->assertDatabaseMissing('tariffs', ['id' => $tariff->id]);
    }
}
