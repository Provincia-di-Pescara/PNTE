<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EntityType;
use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EntityTest extends TestCase
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

    public function test_index_accessible_by_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.entities.index'))
            ->assertOk()
            ->assertViewIs('admin.entities.index');
    }

    public function test_index_forbidden_for_citizen(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('admin.entities.index'))
            ->assertForbidden();
    }

    public function test_create_form_forbidden_for_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.entities.create'))
            ->assertForbidden();
    }

    public function test_create_form_accessible_by_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.entities.create'))
            ->assertOk()
            ->assertViewIs('admin.entities.form');
    }

    public function test_store_creates_entity(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.entities.store'), [
                'nome' => 'Comune di Pescara',
                'tipo' => EntityType::Comune->value,
                'codice_istat' => '068028',
                'pec' => 'comunedipescara@pec.it',
            ])->assertRedirect(route('admin.entities.index'));

        $this->assertDatabaseHas('entities', [
            'nome' => 'Comune di Pescara',
            'tipo' => 'comune',
        ]);
    }

    public function test_store_forbidden_for_operator(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.entities.store'), [
                'nome' => 'Test Comune',
                'tipo' => EntityType::Comune->value,
            ])->assertForbidden();
    }

    public function test_store_validates_tipo_enum(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.entities.store'), [
                'nome' => 'Test',
                'tipo' => 'tipo_inesistente',
            ])->assertSessionHasErrors(['tipo']);
    }

    public function test_store_validates_codice_istat_uniqueness(): void
    {
        Entity::factory()->create(['codice_istat' => '068028']);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.entities.store'), [
                'nome' => 'Altro Comune',
                'tipo' => EntityType::Comune->value,
                'codice_istat' => '068028',
            ])->assertSessionHasErrors(['codice_istat']);
    }

    public function test_show_displays_entity(): void
    {
        $entity = Entity::factory()->create();

        $this->actingAs($this->operator)
            ->get(route('admin.entities.show', $entity))
            ->assertOk()
            ->assertViewIs('admin.entities.show');
    }

    public function test_update_modifies_entity_as_super_admin(): void
    {
        $entity = Entity::factory()->create(['nome' => 'Comune Vecchio']);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.entities.update', $entity), [
                'nome' => 'Comune Aggiornato',
                'tipo' => $entity->tipo->value,
            ])->assertRedirect(route('admin.entities.show', $entity));

        $this->assertDatabaseHas('entities', ['nome' => 'Comune Aggiornato']);
    }

    public function test_update_forbidden_for_operator(): void
    {
        $entity = Entity::factory()->create();

        $this->actingAs($this->operator)
            ->put(route('admin.entities.update', $entity), [
                'nome' => 'Tentativo Modifica',
                'tipo' => $entity->tipo->value,
            ])->assertForbidden();
    }

    public function test_destroy_only_allowed_for_super_admin(): void
    {
        $entity = Entity::factory()->create();

        $this->actingAs($this->operator)
            ->delete(route('admin.entities.destroy', $entity))
            ->assertForbidden();

        $this->assertDatabaseHas('entities', ['id' => $entity->id]);
    }

    public function test_destroy_deletes_entity(): void
    {
        $entity = Entity::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.entities.destroy', $entity))
            ->assertRedirect(route('admin.entities.index'));

        $this->assertDatabaseMissing('entities', ['id' => $entity->id]);
    }
}
