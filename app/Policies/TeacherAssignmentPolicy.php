<?php
namespace App\Policies;
use App\Models\TeacherAssignment;
use App\Models\User;
class TeacherAssignmentPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:TeacherAssignment'); } public function view(User $user, TeacherAssignment $record): bool { return $user->can('View:TeacherAssignment'); } public function create(User $user): bool { return $user->can('Create:TeacherAssignment'); } public function update(User $user, TeacherAssignment $record): bool { return $user->can('Update:TeacherAssignment'); } public function delete(User $user, TeacherAssignment $record): bool { return $user->can('Delete:TeacherAssignment'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:TeacherAssignment'); } }
