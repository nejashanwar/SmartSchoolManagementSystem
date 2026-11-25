<?php

namespace App\Policies;

use App\Models\AdmissionApplication;
use App\Models\User;

class AdmissionApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:AdmissionApplication');
    }

    public function view(User $user, AdmissionApplication $application): bool
    {
        return $user->can('View:AdmissionApplication');
    }

    public function export(User $user): bool
    {
        return $user->can('ViewAny:AdmissionApplication')
            && $user->can('View:AdmissionApplication');
    }

    public function update(User $user, AdmissionApplication $application): bool
    {
        return $user->can('Update:AdmissionApplication');
    }
}
