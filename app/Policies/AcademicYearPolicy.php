<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool { return $user->can('ViewAny:AcademicYear'); }
    public function view(User $user, AcademicYear $academicYear): bool { return $user->can('View:AcademicYear'); }
    public function create(User $user): bool { return $user->can('Create:AcademicYear'); }
    public function update(User $user, AcademicYear $academicYear): bool { return $user->can('Update:AcademicYear'); }
    public function delete(User $user, AcademicYear $academicYear): bool { return $user->can('Delete:AcademicYear'); }
    public function deleteAny(User $user): bool { return $user->can('DeleteAny:AcademicYear'); }
}
