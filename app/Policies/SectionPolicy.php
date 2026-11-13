<?php
namespace App\Policies;
use App\Models\Section;
use App\Models\User;
class SectionPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:Section'); } public function view(User $user, Section $record): bool { return $user->can('View:Section'); } public function create(User $user): bool { return $user->can('Create:Section'); } public function update(User $user, Section $record): bool { return $user->can('Update:Section'); } public function delete(User $user, Section $record): bool { return $user->can('Delete:Section'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:Section'); } }
