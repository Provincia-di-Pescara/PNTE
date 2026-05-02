<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Trip;
use App\Models\User;

final class TripPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Operator->value,
            UserRole::LawEnforcement->value,
        ]);
    }

    public function view(User $user, Trip $trip): bool
    {
        if ($user->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Operator->value,
            UserRole::LawEnforcement->value,
        ])) {
            return true;
        }

        return $user->id === $trip->driver_user_id;
    }

    public function create(User $user): bool
    {
        if (! $user->hasRole(UserRole::Citizen->value)) {
            return false;
        }

        return $user->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists();
    }

    public function update(User $user, Trip $trip): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->id === $trip->driver_user_id;
    }
}
