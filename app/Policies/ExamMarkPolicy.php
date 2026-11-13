<?php
namespace App\Policies;
use App\Models\ExamMark;
use App\Models\User;
class ExamMarkPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:ExamMark'); } public function view(User $user, ExamMark $record): bool { return $user->can('View:ExamMark'); } public function create(User $user): bool { return $user->can('Create:ExamMark'); } public function update(User $user, ExamMark $record): bool { return $user->can('Update:ExamMark'); } public function delete(User $user, ExamMark $record): bool { return $user->can('Delete:ExamMark'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:ExamMark'); } }
