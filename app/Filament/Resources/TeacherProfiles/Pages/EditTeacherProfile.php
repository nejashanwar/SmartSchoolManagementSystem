<?php

namespace App\Filament\Resources\TeacherProfiles\Pages;

use App\Filament\Resources\TeacherProfiles\TeacherProfileResource;
use App\Models\AuditEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeacherProfile extends EditRecord
{
    protected static string $resource = TeacherProfileResource::class;
    protected array $auditBefore = [];

    protected function afterFill(): void
    {
        $this->auditBefore = $this->getRecord()->only(['phone', 'joined_on', 'employment_status']);
    }

    protected function afterSave(): void
    {
        AuditEvent::record('teacher.profile_updated', $this->getRecord(), [
            'before' => $this->auditBefore,
            'after' => $this->getRecord()->only(['phone', 'joined_on', 'employment_status']),
        ]);
    }

    protected function getHeaderActions(): array { return [ViewAction::make()]; }
}
