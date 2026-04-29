<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Roadwork;
use App\Models\User;

final class RoadworkPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Roadwork $roadwork): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ThirdParty->value,
            UserRole::Operator->value,
            UserRole::SuperAdmin->value,
        ]);
    }

    public function update(User $user, Roadwork $roadwork): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $roadwork->entity_id;
    }

    public function delete(User $user, Roadwork $roadwork): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->isThirdParty() && $user->entity_id === $roadwork->entity_id;
    }
}
