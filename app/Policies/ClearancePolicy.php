<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Clearance;
use App\Models\User;

final class ClearancePolicy
{
    public function view(User $user, Clearance $clearance): bool
    {
        if ($user->hasAnyRole(['super-admin', 'operator'])) {
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
