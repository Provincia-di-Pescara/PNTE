<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class OidcCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');

        config(['services.oidc.base_url' => 'https://proxy.example.it']);
    }

    /** Build a fake Socialite OIDC user with Italian fiscal code claim. */
    private function fakeSocialiteUser(
        string $fiscalNumber = 'RSSMRA80A01H501U',
        string $givenName = 'Mario',
        string $familyName = 'Rossi',
        ?string $email = 'mario.rossi@example.it',
        string $sub = 'spid-sub-123',
    ): SocialiteUser {
        $user = new SocialiteUser;
        $user->map([
            'id' => $sub,
            'name' => $givenName.' '.$familyName,
            'email' => $email,
        ]);
        $user->setRaw([
            'sub' => $sub,
            'fiscalNumber' => $fiscalNumber,
            'given_name' => $givenName,
            'family_name' => $familyName,
            'email' => $email,
        ]);

        return $user;
    }

    private function mockOidcDriver(SocialiteUser $socialiteUser): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('oidc')->andReturn($provider);
    }

    public function test_callback_creates_new_citizen_and_logs_in(): void
    {
        $this->mockOidcDriver($this->fakeSocialiteUser());

        $this->get(route('auth.oidc.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where('codice_fiscale', 'RSSMRA80A01H501U')->firstOrFail();
        $this->assertSame('Mario Rossi', $user->name);
        $this->assertSame('mario.rossi@example.it', $user->email);
        $this->assertSame(AuthProvider::Spid, $user->auth_provider);
        $this->assertSame('spid-sub-123', $user->provider_id);
        $this->assertTrue($user->hasRole(UserRole::Citizen->value));
    }

    public function test_callback_does_not_duplicate_existing_user(): void
    {
        $existing = User::factory()->create([
            'codice_fiscale' => 'RSSMRA80A01H501U',
            'auth_provider' => AuthProvider::Spid->value,
            'provider_id' => 'spid-sub-123',
            'email' => null,
        ]);
        $existing->assignRole(UserRole::Citizen->value);

        $this->mockOidcDriver($this->fakeSocialiteUser());

        $this->get(route('auth.oidc.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, User::where('codice_fiscale', 'RSSMRA80A01H501U')->count());
        $this->assertAuthenticatedAs($existing);
    }

    public function test_callback_works_without_email_claim(): void
    {
        $this->mockOidcDriver($this->fakeSocialiteUser(email: null));

        $this->get(route('auth.oidc.callback'))
            ->assertRedirect(route('dashboard'));

        $user = User::where('codice_fiscale', 'RSSMRA80A01H501U')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertTrue($user->hasRole(UserRole::Citizen->value));
    }
}
