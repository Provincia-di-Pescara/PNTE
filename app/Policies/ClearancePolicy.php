<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Clearance;
use App\Models\User;

final class ClearancePolicy
{
    public function view(User $user, Clearance $clearance): bool
    {
        if ($user->isSystemAdmin()) {
            return false;
        }

        if ($user->hasAnyRole([
            UserRole::AdminCapofila->value,
            UserRole::AdminEnte->value,
            UserRole::Operator->value,
        ])) {
            return true;
        }

        if ($user->isThirdParty()) {
            return $user->entity_id === $clearance->entity_id;
        }

        return false;
    }

    public function decide(User $user, Clearance $clearance): bool
    {
        return $user->isThirdParty() && $user->entity_id === $clearance->entity_id;
    }
}
