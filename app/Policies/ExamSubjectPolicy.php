<?php
namespace App\Policies;
use App\Models\ExamSubject;
use App\Models\User;
class ExamSubjectPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:ExamSubject'); } public function view(User $user, ExamSubject $record): bool { return $user->can('View:ExamSubject'); } public function create(User $user): bool { return $user->can('Create:ExamSubject'); } public function update(User $user, ExamSubject $record): bool { return $user->can('Update:ExamSubject'); } public function delete(User $user, ExamSubject $record): bool { return $user->can('Delete:ExamSubject'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:ExamSubject'); } }
