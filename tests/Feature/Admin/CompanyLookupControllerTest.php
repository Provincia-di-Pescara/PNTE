<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Contracts\InfoCamereServiceInterface;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CompanyLookupControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminCapofila;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $this->adminCapofila = User::factory()->create();
        $this->adminCapofila->assignRole(UserRole::AdminCapofila->value);
    }

    public function test_returns_503_when_pdnd_is_disabled(): void
    {
        Setting::set('pdnd_enabled', '0', 'pdnd');

        $response = $this->actingAs($this->adminCapofila)
            ->postJson(route('api.admin.companies.lookup'), ['piva' => '12345678901']);

        $response->assertStatus(503)
            ->assertJsonFragment(['code' => 'pdnd_disabled']);
    }

    public function test_returns_422_for_invalid_piva(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');

        $response = $this->actingAs($this->adminCapofila)
            ->postJson(route('api.admin.companies.lookup'), ['piva' => 'INVALID']);

        $response->assertStatus(422);
    }

    public function test_returns_company_data_on_success(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');

        $fakeData = [
            'ragione_sociale' => 'ACME S.R.L.',
            'codice_fiscale' => '12345678901',
            'indirizzo' => 'Via Roma 1',
            'comune' => 'Pescara',
            'cap' => '65121',
            'provincia' => 'PE',
            'email' => 'info@acme.it',
            'pec' => 'acme@pec.it',
        ];

        $mock = Mockery::mock(InfoCamereServiceInterface::class);
        $mock->shouldReceive('getByPiva')->once()->with('12345678901')->andReturn($fakeData);
        $this->app->instance(InfoCamereServiceInterface::class, $mock);

        $response = $this->actingAs($this->adminCapofila)
            ->postJson(route('api.admin.companies.lookup'), ['piva' => '12345678901']);

        $response->assertOk()
            ->assertJsonPath('data.ragione_sociale', 'ACME S.R.L.')
            ->assertJsonPath('data.pec', 'acme@pec.it');
    }

    public function test_returns_404_when_company_not_found(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');

        $mock = Mockery::mock(InfoCamereServiceInterface::class);
        $mock->shouldReceive('getByPiva')->once()->andThrow(
            new RuntimeException('Impresa con P.IVA 00000000000 non trovata nel Registro Imprese.')
        );
        $this->app->instance(InfoCamereServiceInterface::class, $mock);

        $response = $this->actingAs($this->adminCapofila)
            ->postJson(route('api.admin.companies.lookup'), ['piva' => '00000000000']);

        $response->assertStatus(404)
            ->assertJsonFragment(['code' => 'not_found']);
    }

    public function test_returns_502_on_api_error(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');

        $mock = Mockery::mock(InfoCamereServiceInterface::class);
        $mock->shouldReceive('getByPiva')->once()->andThrow(
            new RuntimeException('HTTP 500')
        );
        $this->app->instance(InfoCamereServiceInterface::class, $mock);

        $response = $this->actingAs($this->adminCapofila)
            ->postJson(route('api.admin.companies.lookup'), ['piva' => '12345678901']);

        $response->assertStatus(502)
            ->assertJsonFragment(['code' => 'api_error']);
    }

    public function test_unauthenticated_user_gets_json_401(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');

        $response = $this->postJson(route('api.admin.companies.lookup'), ['piva' => '12345678901']);

        $response->assertStatus(401);
    }

    public function test_store_company_sets_infocamere_verified_at_when_verified(): void
    {
        $response = $this->actingAs($this->adminCapofila)
            ->post(route('admin.companies.store'), [
                'ragione_sociale' => 'Test S.R.L.',
                'partita_iva' => '12345678901',
                'infocamere_verified' => '1',
            ]);

        $response->assertRedirect();

        $company = Company::where('partita_iva', '12345678901')->first();
        $this->assertNotNull($company);
        $this->assertNotNull($company->infocamere_verified_at);
    }

    public function test_store_company_without_verification_leaves_verified_at_null(): void
    {
        $response = $this->actingAs($this->adminCapofila)
            ->post(route('admin.companies.store'), [
                'ragione_sociale' => 'Test S.R.L. 2',
                'partita_iva' => '98765432100',
                'infocamere_verified' => '0',
            ]);

        $response->assertRedirect();

        $company = Company::where('partita_iva', '98765432100')->first();
        $this->assertNotNull($company);
        $this->assertNull($company->infocamere_verified_at);
    }
}
