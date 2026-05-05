<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Route as RouteModel;
use App\Models\User;

final class RoutePolicy
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

        return $user->hasAnyRole(array_merge($this->managers, [UserRole::Citizen->value]));
    }

    public function view(User $user, RouteModel $route): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers)
            || $route->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->isCitizen() || $user->hasAnyRole($this->managers);
    }

    public function update(User $user, RouteModel $route): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers)
            || $route->user_id === $user->id;
    }

    public function delete(User $user, RouteModel $route): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole($this->managers)
            || $route->user_id === $user->id;
    }
}
