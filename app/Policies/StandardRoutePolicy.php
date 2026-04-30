<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StandardRoute;
use App\Models\User;

final class StandardRoutePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Operator->value,
            UserRole::ThirdParty->value,
        ]);
    }

    public function view(User $user, StandardRoute $standardRoute): bool
    {
        return $user->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Operator->value,
            UserRole::ThirdParty->value,
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ThirdParty->value,
            UserRole::Operator->value,
            UserRole::SuperAdmin->value,
        ]);
    }

    public function update(User $user, StandardRoute $standardRoute): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $standardRoute->entity_id;
    }

    public function delete(User $user, StandardRoute $standardRoute): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $standardRoute->entity_id;
    }
}
