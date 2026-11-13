<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool { return $user->can('ViewAny:Grade'); }
    public function view(User $user, Grade $grade): bool { return $user->can('View:Grade'); }
    public function create(User $user): bool { return $user->can('Create:Grade'); }
    public function update(User $user, Grade $grade): bool { return $user->can('Update:Grade'); }
    public function delete(User $user, Grade $grade): bool { return $user->can('Delete:Grade'); }
    public function deleteAny(User $user): bool { return $user->can('DeleteAny:Grade'); }
}
