<?php
namespace App\Filament\Resources\TeacherAssignments\Pages;
use App\Filament\Resources\TeacherAssignments\TeacherAssignmentResource;
use App\Models\ExamSubject;
use App\Models\TeacherAssignment;
use App\Services\SyncExamSubjectsFromAssignments;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
class EditTeacherAssignment extends EditRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $scopeChanged = (int) $record->section_id !== (int) $data['section_id']
            || (int) $record->subject_id !== (int) $data['subject_id']
            || (int) $record->term_id !== (int) $data['term_id'];

        if ($scopeChanged) {
            $hasRegisters = ExamSubject::query()->where('subject_id', $record->subject_id)
                ->whereHas('exam', function ($query) use ($record): void {
                    $query->where('section_id', $record->section_id);
                    if ($record->term_id) $query->where('term_id', $record->term_id);
                })->exists();

            if ($hasRegisters) {
                throw ValidationException::withMessages(['data.term_id' => 'Remove the related examination subject registers before changing this assignment’s section, subject, or semester.']);
            }
        }

        if (TeacherAssignment::query()->where('section_id', $data['section_id'])->where('subject_id', $data['subject_id'])->where('term_id', $data['term_id'])->where('id', '!=', $record->id)->exists()) {
            throw ValidationException::withMessages(['data.subject_id' => 'This subject already has a teacher assignment for the selected section and semester.']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(SyncExamSubjectsFromAssignments::class)->forAssignment($this->record);
    }
}
