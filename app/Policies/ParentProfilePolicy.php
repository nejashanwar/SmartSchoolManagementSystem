<?php
namespace App\Policies;
use App\Models\ParentProfile;
use App\Models\User;
class ParentProfilePolicy { public function viewAny(User $user):bool{return $user->can('ViewAny:ParentProfile');} public function view(User $user,ParentProfile $record):bool{return $user->can('View:ParentProfile');} public function create(User $user):bool{return $user->can('Create:ParentProfile');} public function update(User $user,ParentProfile $record):bool{return $user->can('Update:ParentProfile');} public function delete(User $user,ParentProfile $record):bool{return $user->can('Delete:ParentProfile');} public function deleteAny(User $user):bool{return $user->can('DeleteAny:ParentProfile');} }
