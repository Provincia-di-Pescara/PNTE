<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Route as RouteModel;
use App\Models\User;

final class RoutePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value, UserRole::Citizen->value]);
    }

    public function view(User $user, RouteModel $route): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])
            || $route->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCitizen() || $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function update(User $user, RouteModel $route): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])
            || $route->user_id === $user->id;
    }

    public function delete(User $user, RouteModel $route): bool
    {
        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])
            || $route->user_id === $user->id;
    }
}
