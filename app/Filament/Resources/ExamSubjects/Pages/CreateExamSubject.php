<?php
namespace App\Filament\Resources\ExamSubjects\Pages;
use App\Filament\Resources\ExamSubjects\ExamSubjectResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;
class CreateExamSubject extends CreateRecord { protected static string $resource=ExamSubjectResource::class; protected function afterCreate(): void { AuditEvent::record('exam.subject_added',$this->record,$this->record->only(['exam_id','subject_id','max_marks'])); } }
