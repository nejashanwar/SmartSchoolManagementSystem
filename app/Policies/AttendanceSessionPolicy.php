<?php
namespace App\Policies;
use App\Models\AttendanceSession;
use App\Models\User;
class AttendanceSessionPolicy { public function viewAny(User $user): bool{return $user->can('ViewAny:AttendanceSession');} public function view(User $user,AttendanceSession $record):bool{return $user->can('View:AttendanceSession');} public function create(User $user):bool{return $user->can('Create:AttendanceSession');} public function update(User $user,AttendanceSession $record):bool{return $user->can('Update:AttendanceSession');} public function delete(User $user,AttendanceSession $record):bool{return $user->can('Delete:AttendanceSession');} public function deleteAny(User $user):bool{return $user->can('DeleteAny:AttendanceSession');} }
