<?php
namespace App\Filament\Resources\Exams\Pages;
use App\Filament\Resources\Exams\ExamResource;
use App\Models\AuditEvent;
use App\Services\SyncExamSubjectsFromAssignments;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Exam;
use Illuminate\Validation\ValidationException;
class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $exists = Exam::query()
            ->where('section_id', $data['section_id'])
            ->where('term_id', $data['term_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['data.term_id' => 'Create only one examination register for each section and semester. Add every subject to that semester register.']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(SyncExamSubjectsFromAssignments::class)->forExam($this->record);
        AuditEvent::record('exam.created', $this->record, $this->record->only(['name', 'academic_year_id', 'term_id', 'section_id', 'exam_date']));
    }
}
