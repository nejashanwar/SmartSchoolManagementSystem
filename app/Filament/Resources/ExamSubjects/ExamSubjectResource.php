<?php
namespace App\Filament\Resources\ExamSubjects;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Subject;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;
use App\Models\AuditEvent;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
class ExamSubjectResource extends Resource {
 protected static ?string $model=ExamSubject::class; protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedBookOpen; protected static string|UnitEnum|null $navigationGroup='Attendance & exams'; protected static ?string $navigationLabel='Examination subjects'; protected static ?int $navigationSort=3;
 public static function form(Schema $schema): Schema { return $schema->components([
  Select::make('exam_id')->label('Examination')->options(fn()=>Exam::query()->with(['section.grade'])->whereNotIn('status',['completed','published'])->orderByDesc('exam_date')->get()->mapWithKeys(fn($e)=>[$e->id=>$e->name.' · '.$e->section->grade->name.' '.$e->section->name])->all())->required()->searchable(),
  Select::make('subject_id')->label('Subject')->options(fn()=>Subject::query()->where('is_active',true)->orderBy('name')->pluck('name','id'))->required()->searchable(),
  Hidden::make('max_marks')->default(100),
 ]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('exam.name')->label('Examination')->searchable(),TextColumn::make('exam.section.name')->label('Section'),TextColumn::make('subject.name')->label('Subject')->searchable(),TextColumn::make('marks_status')->label('Marks register')->badge(),TextColumn::make('reviewer.name')->label('Reviewed by')->placeholder('—')])->recordActions([
  Action::make('approveMarks')->label('Approve marks')->color('success')->visible(fn(ExamSubject $record)=>$record->marks_status==='submitted')->authorize(fn()=>auth()->user()?->can('Update:ExamSubject')??false)->requiresConfirmation()->action(function(ExamSubject $record):void{$record->load(['exam.section.students','components']);$expected=$record->exam->section->students->count()*$record->components->count();$submitted=$record->marks()->where('status','submitted')->count();abort_if($expected===0||$submitted!==$expected,422,'The marks register is incomplete. Return it to the teacher for correction.');$record->marks()->update(['status'=>'verified']);$record->update(['marks_status'=>'approved','reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_note'=>null]);AuditEvent::record('exam.marks_approved',$record,['approved_by'=>auth()->id(),'mark_count'=>$submitted]);Notification::make()->success()->title('Marks approved')->body('The result can now be generated.')->send();}),
  Action::make('returnMarks')->label('Return for correction')->color('warning')->visible(fn(ExamSubject $record)=>$record->marks_status==='submitted')->authorize(fn()=>auth()->user()?->can('Update:ExamSubject')??false)->schema([Textarea::make('review_note')->label('Correction notes')->required()->maxLength(2000)])->action(function(ExamSubject $record,array $data):void{$record->update(['marks_status'=>'returned','reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_note'=>$data['review_note']]);AuditEvent::record('exam.marks_returned',$record,['returned_by'=>auth()->id(),'note'=>$data['review_note']]);Notification::make()->warning()->title('Marks returned to teacher')->send();}),
  \Filament\Actions\EditAction::make(),\Filament\Actions\DeleteAction::make()
 ])->filters([SelectFilter::make('marks_status')->label('Register status')->options(['draft'=>'Draft','submitted'=>'Submitted','returned'=>'Returned','approved'=>'Approved'])])->toolbarActions([]); }
 public static function getPages(): array { return ['index'=>Pages\ListExamSubjects::route('/'),'create'=>Pages\CreateExamSubject::route('/create'),'edit'=>Pages\EditExamSubject::route('/{record}/edit')]; }
 public static function canEdit(Model $record):bool{return !in_array($record->exam->status,['completed','published'],true)&&!$record->marks()->exists();}
 public static function canDelete(Model $record):bool{return !in_array($record->exam->status,['completed','published'],true)&&!$record->marks()->exists();}
}
