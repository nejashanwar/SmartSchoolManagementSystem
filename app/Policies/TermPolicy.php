<?php
namespace App\Policies;
use App\Models\Term;
use App\Models\User;
class TermPolicy { public function viewAny(User $user): bool { return $user->can('ViewAny:Term'); } public function view(User $user, Term $record): bool { return $user->can('View:Term'); } public function create(User $user): bool { return $user->can('Create:Term'); } public function update(User $user, Term $record): bool { return $user->can('Update:Term'); } public function delete(User $user, Term $record): bool { return $user->can('Delete:Term'); } public function deleteAny(User $user): bool { return $user->can('DeleteAny:Term'); } }
