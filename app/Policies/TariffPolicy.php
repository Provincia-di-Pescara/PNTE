<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Tariff;
use App\Models\User;

final class TariffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
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
        return $this->viewAny($user);
    }
}
