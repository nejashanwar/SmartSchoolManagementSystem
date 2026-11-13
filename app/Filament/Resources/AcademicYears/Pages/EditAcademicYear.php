<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use App\Models\AuditEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicYear extends EditRecord
{
    protected static string $resource = AcademicYearResource::class;
    protected array $auditBefore = [];

    protected function afterFill(): void
    {
        $this->auditBefore = $this->getRecord()->only([
            'name', 'starts_on', 'ends_on', 'admission_opens_at', 'admission_closes_at', 'is_active',
        ]);
    }

    protected function afterSave(): void
    {
        AuditEvent::record('academic_year.updated', $this->getRecord(), [
            'before' => $this->auditBefore,
            'after' => $this->getRecord()->only([
                'name', 'starts_on', 'ends_on', 'admission_opens_at', 'admission_closes_at', 'is_active',
            ]),
        ]);
    }

    protected function getHeaderActions(): array { return [ViewAction::make()]; }
}
