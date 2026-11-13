<?php
namespace App\Policies;
use App\Models\ExamType;
use App\Models\User;
class ExamTypePolicy { public function viewAny(User $user):bool{return $user->can('ViewAny:ExamType');} public function view(User $user,ExamType $record):bool{return $user->can('View:ExamType');} public function create(User $user):bool{return $user->can('Create:ExamType');} public function update(User $user,ExamType $record):bool{return $user->can('Update:ExamType');} public function delete(User $user,ExamType $record):bool{return $user->can('Delete:ExamType');} public function deleteAny(User $user):bool{return $user->can('DeleteAny:ExamType');} }
