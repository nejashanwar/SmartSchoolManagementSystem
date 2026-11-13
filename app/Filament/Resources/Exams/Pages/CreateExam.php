<?php
namespace App\Filament\Resources\Exams\Pages;
use App\Filament\Resources\Exams\ExamResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;
class CreateExam extends CreateRecord { protected static string $resource=ExamResource::class; protected function afterCreate(): void { AuditEvent::record('exam.created',$this->record,$this->record->only(['name','academic_year_id','term_id','section_id','exam_date'])); } }
