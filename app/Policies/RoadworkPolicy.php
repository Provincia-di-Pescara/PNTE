<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Roadwork;
use App\Models\User;

final class RoadworkPolicy
{
    /** @var list<string> */
    private array $enteManagers = [
        UserRole::AdminCapofila->value,
        UserRole::AdminEnte->value,
        UserRole::Operator->value,
    ];

    public function viewAny(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole(array_merge($this->enteManagers, [
            UserRole::ThirdParty->value,
            UserRole::LawEnforcement->value,
        ]));
    }

    public function view(User $user, Roadwork $roadwork): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole(array_merge($this->enteManagers, [
            UserRole::ThirdParty->value,
            UserRole::LawEnforcement->value,
        ]));
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole(array_merge($this->enteManagers, [UserRole::ThirdParty->value]));
    }

    public function update(User $user, Roadwork $roadwork): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole($this->enteManagers)) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $roadwork->entity_id;
    }

    public function delete(User $user, Roadwork $roadwork): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole($this->enteManagers)) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $roadwork->entity_id;
    }
}
