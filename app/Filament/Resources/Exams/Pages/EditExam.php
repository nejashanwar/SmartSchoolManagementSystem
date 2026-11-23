<?php
namespace App\Filament\Resources\Exams\Pages;
use App\Filament\Resources\Exams\ExamResource;
use App\Models\AuditEvent;
use App\Models\Exam;
use App\Models\User;
use App\Services\GenerateExamResults;
use App\Services\SyncExamSubjectsFromAssignments;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
class EditExam extends EditRecord {
 protected static string $resource=ExamResource::class;
 protected function mutateFormDataBeforeSave(array $data): array {
  $record=$this->getRecord();
  $changed=(int)$record->section_id!==(int)$data['section_id']||(int)$record->term_id!==(int)$data['term_id'];
  if($changed&&$record->subjects()->exists())throw ValidationException::withMessages(['data.term_id'=>'Remove this examination’s subject registers before changing its semester or section.']);
  $duplicate=Exam::query()->where('section_id',$data['section_id'])->where('term_id',$data['term_id'])->where('id','!=',$record->id)->exists();
  if($duplicate)throw ValidationException::withMessages(['data.term_id'=>'Only one examination register is allowed for each section and semester.']);
  return $data;
 }
 protected function afterSave(): void { app(SyncExamSubjectsFromAssignments::class)->forExam($this->record); AuditEvent::record('exam.updated',$this->record,$this->record->only(['name','exam_type_id','academic_year_id','term_id','section_id','exam_date','status'])); }
 protected function getHeaderActions():array{return [
  Action::make('generateResults')->label('Generate results')->color('warning')->visible(fn()=>auth()->user()?->can('Generate:Result')&&$this->getRecord()->status!=='published')->authorize(fn()=>auth()->user()?->can('Generate:Result')??false)->requiresConfirmation()->action(function(){
   $actor=auth()->user();abort_unless($actor instanceof User,403);
   try{$count=app(GenerateExamResults::class)->generate($this->getRecord(),$actor);Notification::make()->success()->title('Draft results generated')->body("Generated results for {$count} students. Review the draft results before publishing.")->send();}
   catch(ValidationException $exception){Notification::make()->warning()->title('Results could not be generated')->body(collect($exception->errors())->flatten()->implode(' '))->persistent()->send();}
  }),
  Action::make('publishResults')->label('Publish results')->color('success')->visible(fn()=>auth()->user()?->can('Publish:Result')&&$this->getRecord()->status!=='published')->authorize(fn()=>auth()->user()?->can('Publish:Result')??false)->requiresConfirmation()->action(function(){
   $actor=auth()->user();abort_unless($actor instanceof User,403);
   try{$count=app(GenerateExamResults::class)->publish($this->getRecord(),$actor);Notification::make()->success()->title('Results published')->body("Published results for {$count} students. Students and parents can now view the report cards.")->send();}
   catch(ValidationException $exception){Notification::make()->warning()->title('Results could not be published')->body(collect($exception->errors())->flatten()->implode(' '))->persistent()->send();}
  }),
 ];}
}
