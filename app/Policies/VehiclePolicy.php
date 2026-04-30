<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Models\Vehicle;

final class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $this->citizenHasApprovedDelegationFor($user, $vehicle->company);
    }

    public function create(User $user): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $user->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $this->citizenHasApprovedDelegationFor($user, $vehicle->company);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Operator->value])) {
            return true;
        }

        return $this->citizenHasApprovedDelegationFor($user, $vehicle->company);
    }

    private function citizenHasApprovedDelegationFor(User $user, Company $company): bool
    {
        return $user->companies()
            ->where('company_id', $company->id)
            ->whereNotNull('company_user.approved_at')
            ->exists();
    }
}
