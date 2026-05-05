<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Tariff;
use App\Models\User;

final class TariffPolicy
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

        return $user->hasAnyRole($this->managers);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Tariff $tariff): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Tariff $tariff): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        return $user->hasAnyRole([UserRole::AdminCapofila->value, UserRole::AdminEnte->value]);
    }
}
