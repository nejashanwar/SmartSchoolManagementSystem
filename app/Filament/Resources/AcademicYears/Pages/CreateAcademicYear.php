<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    protected function afterCreate(): void
    {
        AuditEvent::record('academic_year.created', $this->record, $this->record->only([
            'name', 'starts_on', 'ends_on', 'admission_opens_at', 'admission_closes_at', 'is_active',
        ]));
    }
}
