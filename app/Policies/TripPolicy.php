<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Trip;
use App\Models\User;

final class TripPolicy
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

        return $user->hasAnyRole(array_merge($this->managers, [UserRole::LawEnforcement->value]));
    }

    public function view(User $user, Trip $trip): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole(array_merge($this->managers, [UserRole::LawEnforcement->value]))) {
            return true;
        }

        return $user->id === $trip->driver_user_id;
    }

    public function create(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if (! $user->hasRole(UserRole::Citizen->value)) {
            return false;
        }

        return $user->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists();
    }

    public function update(User $user, Trip $trip): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole($this->managers)) {
            return true;
        }

        return $user->id === $trip->driver_user_id;
    }
}
