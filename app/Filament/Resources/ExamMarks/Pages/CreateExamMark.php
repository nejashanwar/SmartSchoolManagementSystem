<?php
namespace App\Filament\Resources\ExamMarks\Pages;
use App\Filament\Resources\ExamMarks\ExamMarkResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;
class CreateExamMark extends CreateRecord { protected static string $resource=ExamMarkResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $component=\App\Models\ExamAssessmentComponent::findOrFail($data['assessment_component_id']);$data['exam_subject_id']=$component->exam_subject_id;$data['entered_by']=auth()->id();return $data; } protected function afterCreate(): void { AuditEvent::record('exam.mark_entered',$this->record,$this->record->only(['exam_subject_id','assessment_component_id','student_id','marks_obtained','status'])); } }
