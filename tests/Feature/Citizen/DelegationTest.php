<?php

declare(strict_types=1);

namespace Tests\Feature\Citizen;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DelegationTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::Citizen->value);
    }

    public function test_unauthenticated_user_cannot_access_delegation_routes(): void
    {
        $this->get(route('my.delegations.index'))->assertRedirect(route('login'));
        $this->get(route('my.delegations.create'))->assertRedirect(route('login'));
    }

    public function test_citizen_can_view_own_delegations(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('my.delegations.index'))
            ->assertOk()
            ->assertViewIs('citizen.delegations.index');
    }

    public function test_citizen_can_see_create_form(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('my.delegations.create'))
            ->assertOk()
            ->assertViewIs('citizen.delegations.create');
    }

    public function test_lookup_returns_existing_company(): void
    {
        $company = Company::factory()->create([
            'ragione_sociale' => 'Trasporti Bianchi S.r.l.',
            'partita_iva' => '12345678901',
        ]);

        $this->actingAs($this->citizen)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->postJson(route('my.delegations.lookup'), ['partita_iva' => '12345678901'])
            ->assertOk()
            ->assertJson([
                'found' => true,
                'company' => [
                    'ragione_sociale' => 'Trasporti Bianchi S.r.l.',
                ],
            ]);
    }

    public function test_lookup_returns_not_found_for_unknown_piva(): void
    {
        $this->actingAs($this->citizen)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->postJson(route('my.delegations.lookup'), ['partita_iva' => '99999999999'])
            ->assertOk()
            ->assertJson(['found' => false]);
    }

    public function test_citizen_can_request_delegation_to_existing_company(): void
    {
        $company = Company::factory()->create(['partita_iva' => '12345678901']);

        $this->actingAs($this->citizen)
            ->post(route('my.delegations.store'), [
                'partita_iva' => '12345678901',
                'ragione_sociale' => $company->ragione_sociale,
                'valid_from' => today()->toDateString(),
                'valid_to' => null,
            ])
            ->assertRedirect(route('my.delegations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $this->citizen->id,
            'role' => 'delegato',
        ]);
    }

    public function test_citizen_can_request_delegation_creating_new_company(): void
    {
        $this->actingAs($this->citizen)
            ->post(route('my.delegations.store'), [
                'partita_iva' => '11122233344',
                'ragione_sociale' => 'Nuova Azienda S.r.l.',
                'comune' => 'Pescara',
                'provincia' => 'PE',
                'valid_from' => today()->toDateString(),
            ])
            ->assertRedirect(route('my.delegations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('companies', [
            'partita_iva' => '11122233344',
            'ragione_sociale' => 'Nuova Azienda S.r.l.',
        ]);

        $company = Company::where('partita_iva', '11122233344')->firstOrFail();

        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $this->citizen->id,
            'role' => 'delegato',
        ]);
    }

    public function test_citizen_cannot_request_duplicate_delegation(): void
    {
        $company = Company::factory()->create(['partita_iva' => '12345678901']);

        $company->users()->attach($this->citizen->id, [
            'role' => 'delegato',
            'valid_from' => today()->toDateString(),
        ]);

        $this->actingAs($this->citizen)
            ->post(route('my.delegations.store'), [
                'partita_iva' => '12345678901',
                'ragione_sociale' => $company->ragione_sociale,
                'valid_from' => today()->toDateString(),
            ])
            ->assertSessionHasErrors(['partita_iva']);
    }
}
