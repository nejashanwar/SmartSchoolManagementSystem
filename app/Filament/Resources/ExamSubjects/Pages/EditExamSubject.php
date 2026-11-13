<?php
namespace App\Filament\Resources\ExamSubjects\Pages;
use App\Filament\Resources\ExamSubjects\ExamSubjectResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
class EditExamSubject extends EditRecord { protected static string $resource=ExamSubjectResource::class; protected function afterSave(): void { AuditEvent::record('exam.subject_updated',$this->record,$this->record->only(['exam_id','subject_id','max_marks'])); } }
