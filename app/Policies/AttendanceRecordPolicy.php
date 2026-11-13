<?php
namespace App\Policies;
use App\Models\AttendanceRecord;
use App\Models\User;
class AttendanceRecordPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:AttendanceRecord'); } public function view(User $user, AttendanceRecord $record): bool { return $user->can('View:AttendanceRecord'); } public function create(User $user): bool { return $user->can('Create:AttendanceRecord'); } public function update(User $user, AttendanceRecord $record): bool { return $user->can('Update:AttendanceRecord'); } public function delete(User $user, AttendanceRecord $record): bool { return $user->can('Delete:AttendanceRecord'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:AttendanceRecord'); } }
