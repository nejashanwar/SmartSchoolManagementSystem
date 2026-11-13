<?php
namespace App\Policies;
use App\Models\Result;
use App\Models\User;
class ResultPolicy { public function viewAny(User $user):bool{return $user->can('ViewAny:Result');} public function view(User $user,Result $record):bool{return $user->can('View:Result');} public function generate(User $user):bool{return $user->can('Generate:Result');} public function publish(User $user,Result $record):bool{return $user->can('Publish:Result');} }
