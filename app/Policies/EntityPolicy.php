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
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([
            UserRole::AdminCapofila->value,
            UserRole::AdminEnte->value,
            UserRole::Operator->value,
        ]);
    }

    public function view(User $user, Entity $entity): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([
            UserRole::AdminCapofila->value,
            UserRole::AdminEnte->value,
            UserRole::Operator->value,
        ]);
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([UserRole::AdminCapofila->value, UserRole::AdminEnte->value]);
    }

    public function update(User $user, Entity $entity): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([UserRole::AdminCapofila->value, UserRole::AdminEnte->value]);
    }

    public function delete(User $user, Entity $entity): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasRole(UserRole::AdminCapofila->value);
    }
}
