<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Entity;
use App\Models\User;

final class EntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function view(User $user, Entity $entity): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin->value);
    }

    public function update(User $user, Entity $entity): bool
    {
        return $user->hasRole(UserRole::SuperAdmin->value);
    }

    public function delete(User $user, Entity $entity): bool
    {
        return $user->hasRole(UserRole::SuperAdmin->value);
    }
}
