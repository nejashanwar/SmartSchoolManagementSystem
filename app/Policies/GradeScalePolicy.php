<?php
namespace App\Policies;
use App\Models\GradeScale;
use App\Models\User;
class GradeScalePolicy { public function viewAny(User $user):bool{return $user->can('ViewAny:GradeScale');} public function view(User $user,GradeScale $record):bool{return $user->can('View:GradeScale');} public function create(User $user):bool{return $user->can('Create:GradeScale');} public function update(User $user,GradeScale $record):bool{return $user->can('Update:GradeScale');} public function delete(User $user,GradeScale $record):bool{return $user->can('Delete:GradeScale');} public function deleteAny(User $user):bool{return $user->can('DeleteAny:GradeScale');} }
