<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool { return $user->can('ViewAny:Student'); }
    public function view(User $user, Student $student): bool { return $user->can('View:Student'); }
    public function update(User $user, Student $student): bool { return $user->can('Update:Student'); }
}
