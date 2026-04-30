<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CompanyTest extends TestCase
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

    public function test_index_accessible_by_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.companies.index'))
            ->assertOk()
            ->assertViewIs('admin.companies.index');
    }

    public function test_index_accessible_by_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.companies.index'))
            ->assertOk();
    }

    public function test_index_forbidden_for_citizen(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('admin.companies.index'))
            ->assertForbidden();
    }

    public function test_create_form_accessible_by_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.companies.create'))
            ->assertOk()
            ->assertViewIs('admin.companies.form');
    }

    public function test_create_form_forbidden_for_citizen(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('admin.companies.create'))
            ->assertForbidden();
    }

    public function test_store_creates_company(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.companies.store'), [
                'ragione_sociale' => 'Trasporti Rossi S.r.l.',
                'partita_iva' => '12345678901',
                'comune' => 'Pescara',
                'provincia' => 'PE',
            ])->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseHas('companies', [
            'ragione_sociale' => 'Trasporti Rossi S.r.l.',
            'partita_iva' => '12345678901',
        ]);
    }

    public function test_store_validates_partita_iva_format(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.companies.store'), [
                'ragione_sociale' => 'Test S.r.l.',
                'partita_iva' => 'LETTERE123',
            ])->assertSessionHasErrors(['partita_iva']);
    }

    public function test_store_validates_partita_iva_uniqueness(): void
    {
        Company::factory()->create(['partita_iva' => '12345678901']);

        $this->actingAs($this->operator)
            ->post(route('admin.companies.store'), [
                'ragione_sociale' => 'Altra S.r.l.',
                'partita_iva' => '12345678901',
            ])->assertSessionHasErrors(['partita_iva']);
    }

    public function test_show_displays_company_with_users(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->superAdmin)
            ->get(route('admin.companies.show', $company))
            ->assertOk()
            ->assertViewIs('admin.companies.show');
    }

    public function test_update_modifies_company(): void
    {
        $company = Company::factory()->create(['ragione_sociale' => 'Vecchio Nome S.r.l.']);

        $this->actingAs($this->operator)
            ->put(route('admin.companies.update', $company), [
                'ragione_sociale' => 'Nuovo Nome S.r.l.',
                'partita_iva' => $company->partita_iva,
            ])->assertRedirect(route('admin.companies.show', $company));

        $this->assertDatabaseHas('companies', ['ragione_sociale' => 'Nuovo Nome S.r.l.']);
    }

    public function test_destroy_only_allowed_for_super_admin(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->operator)
            ->delete(route('admin.companies.destroy', $company))
            ->assertForbidden();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_destroy_deletes_company_as_super_admin(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.companies.destroy', $company))
            ->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_approve_delegation(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->assignRole(UserRole::Citizen->value);

        $company->users()->attach($user->id, [
            'role' => 'delegato',
            'valid_from' => today(),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.companies.delegation.action', [$company, $user]), [
                'action' => 'approve',
            ])->assertRedirect(route('admin.companies.show', $company));

        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);
        $this->assertNotNull(
            $company->users()->where('user_id', $user->id)->first()?->pivot->approved_at
        );
    }

    public function test_reject_delegation_removes_pivot(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $company->users()->attach($user->id, ['role' => 'delegato', 'valid_from' => today()]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.companies.delegation.action', [$company, $user]), [
                'action' => 'reject',
            ])->assertRedirect(route('admin.companies.show', $company));

        $this->assertDatabaseMissing('company_user', [
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);
    }
}
