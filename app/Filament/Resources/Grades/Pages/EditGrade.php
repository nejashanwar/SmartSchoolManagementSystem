<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\AuditEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;
    protected array $auditBefore = [];

    protected function afterFill(): void
    {
        $this->auditBefore = $this->getRecord()->only(['name', 'sort_order', 'is_active']);
    }

    protected function afterSave(): void
    {
        AuditEvent::record('grade.updated', $this->getRecord(), [
            'before' => $this->auditBefore,
            'after' => $this->getRecord()->only(['name', 'sort_order', 'is_active']),
        ]);
    }

    protected function getHeaderActions(): array { return [ViewAction::make()]; }
}
