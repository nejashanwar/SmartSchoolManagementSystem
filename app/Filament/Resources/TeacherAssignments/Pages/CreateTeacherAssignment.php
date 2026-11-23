<?php
namespace App\Filament\Resources\TeacherAssignments\Pages;
use App\Filament\Resources\TeacherAssignments\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use App\Services\SyncExamSubjectsFromAssignments;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
class CreateTeacherAssignment extends CreateRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (TeacherAssignment::query()->where('section_id', $data['section_id'])->where('subject_id', $data['subject_id'])->where('term_id', $data['term_id'])->exists()) {
            throw ValidationException::withMessages(['data.subject_id' => 'This subject already has a teacher assignment for the selected section and semester.']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(SyncExamSubjectsFromAssignments::class)->forAssignment($this->record);
    }
}
