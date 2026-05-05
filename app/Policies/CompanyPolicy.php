<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

final class CompanyPolicy
{
    /** @var list<string> Roles that can manage companies in the business layer. */
    private array $managers = [
        UserRole::AdminCapofila->value,
        UserRole::AdminEnte->value,
        UserRole::Operator->value,
    ];

    public function viewAny(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers);
    }

    public function view(User $user, Company $company): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers);
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers);
    }

    public function update(User $user, Company $company): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers);
    }

    public function delete(User $user, Company $company): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([UserRole::AdminCapofila->value, UserRole::AdminEnte->value]);
    }

    public function approveDelegation(User $user, Company $company): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers);
    }
}
