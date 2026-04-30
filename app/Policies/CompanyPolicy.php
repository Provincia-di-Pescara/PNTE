<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

final class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function view(User $user, Company $company): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasRole(UserRole::SuperAdmin->value);
    }

    public function approveDelegation(User $user, Company $company): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }
}
