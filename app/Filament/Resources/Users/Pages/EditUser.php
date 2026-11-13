<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditEvent;
use App\Models\TeacherProfile;
use App\Models\ParentProfile;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $auditBefore = [];

    protected function afterFill(): void
    {
        $this->auditBefore = $this->auditState($this->getRecord());
    }

    protected function afterSave(): void
    {
        $user = $this->getRecord()->fresh();

        if ($user->hasRole('PARENT')) {
            ParentProfile::query()->firstOrCreate(['user_id' => $user->getKey()]);
        }

        if ($user->hasRole('TEACHER')) {
            $profile = TeacherProfile::query()->firstOrCreate(
                ['user_id' => $user->getKey()],
                ['employment_status' => 'active'],
            );

            if ($profile->wasRecentlyCreated) {
                AuditEvent::record('teacher.profile_created', $profile, ['user_id' => $user->getKey()]);
            }
        }

        AuditEvent::record('user.updated', $this->getRecord(), [
            'before' => $this->auditBefore,
            'after' => $this->auditState($this->getRecord()),
        ]);
    }

    private function auditState(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => $user->roles->pluck('name')->sort()->values()->all(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
