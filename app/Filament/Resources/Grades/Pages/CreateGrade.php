<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;

    protected function afterCreate(): void
    {
        AuditEvent::record('grade.created', $this->record, $this->record->only(['name', 'sort_order', 'is_active']));
    }
}
