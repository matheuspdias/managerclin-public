<?php

namespace App\Policies;

use App\Models\User;

class BillingPolicy
{
    /**
     * Determine if the user can view billing information.
     */
    public function viewBilling(User $user): bool
    {
        return $user->is_owner && $user->company !== null;
    }

    /**
     * Determine if the user can update billing/subscription.
     */
    public function updateBilling(User $user): bool
    {
        return $user->is_owner && $user->company !== null;
    }
}
