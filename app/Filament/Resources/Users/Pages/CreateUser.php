<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditEvent;
use App\Models\TeacherProfile;
use App\Models\ParentProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->ensureTeacherProfile();
        $this->ensureParentProfile();

        AuditEvent::record('user.created', $this->record, [
            'name' => $this->record->name,
            'email' => $this->record->email,
            'roles' => $this->record->roles->pluck('name')->all(),
        ]);
    }

    private function ensureTeacherProfile(): void
    {
        $user = $this->record->fresh();

        if (! $user?->hasRole('TEACHER')) {
            return;
        }

        $profile = TeacherProfile::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            ['employment_status' => 'active'],
        );

        if ($profile->wasRecentlyCreated) {
            AuditEvent::record('teacher.profile_created', $profile, ['user_id' => $user->getKey()]);
        }
    }

    private function ensureParentProfile(): void
    {
        $user = $this->record->fresh();
        if ($user?->hasRole('PARENT')) {
            ParentProfile::query()->firstOrCreate(['user_id' => $user->getKey()]);
        }
    }
}
