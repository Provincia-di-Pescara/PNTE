<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StandardRoute;
use App\Models\User;

final class StandardRoutePolicy
{
    /** @var list<string> */
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

        return $user->hasAnyRole(array_merge($this->managers, [UserRole::ThirdParty->value]));
    }

    public function view(User $user, StandardRoute $standardRoute): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole(array_merge($this->managers, [UserRole::ThirdParty->value]));
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole(array_merge($this->managers, [UserRole::ThirdParty->value]));
    }

    public function update(User $user, StandardRoute $standardRoute): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole($this->managers)) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $standardRoute->entity_id;
    }

    public function delete(User $user, StandardRoute $standardRoute): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole($this->managers)) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $standardRoute->entity_id;
    }
}
