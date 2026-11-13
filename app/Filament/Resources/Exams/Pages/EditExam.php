<?php
namespace App\Filament\Resources\Exams\Pages;
use App\Filament\Resources\Exams\ExamResource;
use App\Models\AuditEvent;
use App\Models\User;
use App\Services\GenerateExamResults;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
class EditExam extends EditRecord {
 protected static string $resource=ExamResource::class;
 protected function afterSave(): void { AuditEvent::record('exam.updated',$this->record,$this->record->only(['name','exam_type_id','academic_year_id','term_id','section_id','exam_date','status'])); }
 protected function getHeaderActions():array{return [
  Action::make('generateResults')->label('Generate results')->color('warning')->visible(fn()=>auth()->user()?->can('Generate:Result')&&$this->getRecord()->status!=='published')->authorize(fn()=>auth()->user()?->can('Generate:Result')??false)->requiresConfirmation()->action(function(){ $actor=auth()->user();abort_unless($actor instanceof User,403);$count=app(GenerateExamResults::class)->generate($this->getRecord(),$actor);Notification::make()->success()->title('Draft results generated')->body("Generated results for {$count} students. Review the draft results before publishing.")->send(); }),
  Action::make('publishResults')->label('Publish results')->color('success')->visible(fn()=>auth()->user()?->can('Publish:Result')&&$this->getRecord()->status!=='published')->authorize(fn()=>auth()->user()?->can('Publish:Result')??false)->requiresConfirmation()->action(function(){ $actor=auth()->user();abort_unless($actor instanceof User,403);$count=app(GenerateExamResults::class)->publish($this->getRecord(),$actor);Notification::make()->success()->title('Results published')->body("Published results for {$count} students.")->send(); }),
 ];}
}
