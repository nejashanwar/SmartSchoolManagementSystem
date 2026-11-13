<?php
namespace App\Filament\Resources\ExamMarks\Pages;
use App\Filament\Resources\ExamMarks\ExamMarkResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
class EditExamMark extends EditRecord { protected static string $resource=ExamMarkResource::class; protected function mutateFormDataBeforeSave(array $data):array{$component=\App\Models\ExamAssessmentComponent::findOrFail($data['assessment_component_id']);$data['exam_subject_id']=$component->exam_subject_id;return $data;} protected function afterSave(): void { AuditEvent::record('exam.mark_updated',$this->record,$this->record->only(['exam_subject_id','assessment_component_id','student_id','marks_obtained','status'])); } }
