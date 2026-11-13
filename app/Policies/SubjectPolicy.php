<?php
namespace App\Policies;
use App\Models\Subject;
use App\Models\User;
class SubjectPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:Subject'); } public function view(User $user, Subject $record): bool { return $user->can('View:Subject'); } public function create(User $user): bool { return $user->can('Create:Subject'); } public function update(User $user, Subject $record): bool { return $user->can('Update:Subject'); } public function delete(User $user, Subject $record): bool { return $user->can('Delete:Subject'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:Subject'); } }
