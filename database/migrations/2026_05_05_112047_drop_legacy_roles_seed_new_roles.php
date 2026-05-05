<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** @var list<string> */
    private array $legacyRoles = ['super-admin'];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove legacy roles that no longer exist in the new RBAC model.
        // Spatie cascades the deletion to model_has_roles, detaching all affected users.
        foreach ($this->legacyRoles as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->get()->each->delete();
        }

        // Ensure all new roles exist (idempotent — safe to re-run).
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Restore legacy roles.
        foreach ($this->legacyRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Remove roles added in up() that did not exist before.
        $legacySet = array_merge($this->legacyRoles, ['operator', 'third-party', 'citizen', 'law-enforcement']);
        $newOnly = array_filter(
            array_map(fn (UserRole $r) => $r->value, UserRole::cases()),
            fn (string $v) => ! in_array($v, $legacySet, true),
        );
        foreach ($newOnly as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->get()->each->delete();
        }
    }
};
