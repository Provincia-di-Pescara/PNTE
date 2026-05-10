<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\ImportGeoFileJob;
use App\Models\Entity;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SystemAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        // Mark setup as done so unauthenticated requests redirect to login, not /setup
        Setting::set('setup_completed', '1', 'app');
    }

    private function makeUser(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    public function test_system_admin_can_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertOk();
    }

    public function test_system_admin_is_redirected_to_system_dashboard_from_root(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('system.dashboard'));
    }

    public function test_system_admin_can_access_tenants_section(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.tenants'))
            ->assertOk();
    }

    public function test_system_admin_can_toggle_tenant(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $entity = Entity::factory()->create([
            'nome' => 'Provincia Test',
            'is_tenant' => false,
        ]);

        $this->actingAs($user)
            ->post(route('system.tenants.toggle', $entity))
            ->assertRedirect(route('system.tenants'));

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'is_tenant' => true,
        ]);
    }

    public function test_system_admin_can_access_smtp_section(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.smtp'))
            ->assertOk();
    }

    public function test_system_admin_can_access_geo_section(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.geo'))
            ->assertOk();
    }

    public function test_system_admin_can_import_valid_geojson_file(): void
    {
        $this->withoutExceptionHandling();
        Storage::fake('local');
        Queue::fake();

        $user = $this->makeUser(UserRole::SystemAdmin);
        $file = UploadedFile::fake()->createWithContent(
            'boundaries.geojson',
            json_encode(['type' => 'FeatureCollection', 'features' => []])
        );

        $this->actingAs($user)
            ->post(route('system.geo.import'), ['file' => $file])
            ->assertOk()
            ->assertJsonFragment(['ok' => true]);

        Queue::assertPushed(ImportGeoFileJob::class);
    }

    public function test_system_admin_geo_import_rejects_non_json_file(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);
        $file = UploadedFile::fake()->create('report.pdf', 5, 'application/pdf');

        $this->actingAs($user)
            ->post(route('system.geo.import'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_system_admin_can_access_audit_section(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.audit'))
            ->assertOk();
    }

    public function test_system_admin_can_access_release_section(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('system.release'))
            ->assertOk();
    }

    public function test_unauthenticated_redirected_from_system_dashboard(): void
    {
        $this->get(route('system.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_ente_cannot_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::AdminEnte);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_capofila_cannot_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::AdminCapofila);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_citizen_cannot_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::Citizen);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_operator_cannot_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::Operator);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_law_enforcement_cannot_access_system_dashboard(): void
    {
        $user = $this->makeUser(UserRole::LawEnforcement);

        $this->actingAs($user)
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_system_admin_is_blocked_from_admin_area(): void
    {
        $user = $this->makeUser(UserRole::SystemAdmin);

        $this->actingAs($user)
            ->get(route('admin.companies.index'))
            ->assertForbidden();
    }

    public function test_system_admin_can_impersonate(): void
    {
        $systemAdmin = $this->makeUser(UserRole::SystemAdmin);

        $this->assertTrue($systemAdmin->canImpersonate());
    }

    public function test_system_admin_can_be_impersonated_is_false(): void
    {
        $systemAdmin = $this->makeUser(UserRole::SystemAdmin);

        $this->assertFalse($systemAdmin->canBeImpersonated());
    }

    public function test_admin_ente_cannot_be_blocked_by_not_system_admin_middleware(): void
    {
        $user = $this->makeUser(UserRole::AdminEnte);

        // /admin requires admin-capofila|admin-ente|operator AND not-system-admin
        // admin-ente should be allowed in, not blocked
        $this->actingAs($user)
            ->get(route('admin.companies.index'))
            ->assertOk();
    }
}
