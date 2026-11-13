<?php

namespace App\Policies;

use App\Models\TeacherProfile;
use App\Models\User;

class TeacherProfilePolicy
{
    public function viewAny(User $user): bool { return $user->can('ViewAny:TeacherProfile'); }
    public function view(User $user, TeacherProfile $profile): bool { return $user->can('View:TeacherProfile'); }
    public function update(User $user, TeacherProfile $profile): bool { return $user->can('Update:TeacherProfile'); }
}
