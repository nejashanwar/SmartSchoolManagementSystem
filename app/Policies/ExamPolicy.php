<?php
namespace App\Policies;
use App\Models\Exam;
use App\Models\User;
class ExamPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:Exam'); } public function view(User $user, Exam $record): bool { return $user->can('View:Exam'); } public function create(User $user): bool { return $user->can('Create:Exam'); } public function update(User $user, Exam $record): bool { return $user->can('Update:Exam'); } public function delete(User $user, Exam $record): bool { return $user->can('Delete:Exam'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:Exam'); } }
