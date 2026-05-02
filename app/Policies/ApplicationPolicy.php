<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

final class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        if ($user->id === $application->user_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(UserRole::Citizen->value)) {
            return $user->companies()
                ->whereNotNull('company_user.approved_at')
                ->exists();
        }

        return $user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    public function update(User $user, Application $application): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->id === $application->user_id
            && $application->stato === ApplicationStatus::Draft;
    }

    public function delete(User $user, Application $application): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->id === $application->user_id
            && $application->stato === ApplicationStatus::Draft;
    }

    public function decideClearance(User $user, Application $application): bool
    {
        if (! $user->hasRole(UserRole::ThirdParty->value)) {
            return false;
        }

        $entityId = $user->entity_id;

        if ($entityId === null) {
            return false;
        }

        return $application->clearances()
            ->where('entity_id', $entityId)
            ->exists();
    }
}
