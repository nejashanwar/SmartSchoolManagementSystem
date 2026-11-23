<?php
namespace App\Filament\Resources\Exams;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use App\Services\GenerateExamResults;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
class ExamResource extends Resource {
 protected static ?string $model=Exam::class; protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedDocumentText; protected static string|UnitEnum|null $navigationGroup='Attendance & exams'; protected static ?string $navigationLabel='Examinations'; protected static ?int $navigationSort=2;
 public static function form(Schema $schema): Schema { return $schema->components([
  Select::make('academic_year_id')->label('Academic year')->options(fn()=>AcademicYear::query()->orderByDesc('name')->pluck('name','id'))->required()->live()->searchable()->afterStateUpdated(fn($set)=>$set('term_id',null)),
  Select::make('exam_type_id')->label('Examination type')->options(fn()=>ExamType::query()->where('is_active',true)->orderBy('name')->pluck('name','id'))->required()->searchable(),
  Select::make('term_id')->label('Semester')->options(fn($get)=>$get('academic_year_id') ? Term::query()->where('academic_year_id',$get('academic_year_id'))->orderBy('sort_order')->pluck('name','id') : [])->required()->live()->searchable()->rules(fn($get)=>$get('academic_year_id') ? [Rule::exists('terms','id')->where('academic_year_id',$get('academic_year_id'))] : ['required']),
  Select::make('section_id')->label('Section')->options(fn($get)=>$get('academic_year_id') ? Section::query()->with('grade')->where('academic_year_id',$get('academic_year_id'))->where('is_active',true)->get()->mapWithKeys(fn($s)=>[$s->id=>$s->grade->name.' · '.$s->name])->all() : [])->required()->searchable()->rules(fn($get)=>$get('academic_year_id') ? [Rule::exists('sections','id')->where('academic_year_id',$get('academic_year_id'))] : ['required']),
  TextInput::make('name')->required()->maxLength(120), DatePicker::make('exam_date'),
  Select::make('status')->options(fn($record)=>$record?->status==='published'?['published'=>'Published']:['draft'=>'Draft','scheduled'=>'Scheduled','in_progress'=>'In progress','completed'=>'Completed'])->default('draft')->required()->disabled(fn($record)=>$record?->status==='published'),
 ]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('name')->searchable()->sortable(),TextColumn::make('examType.name')->label('Type'),TextColumn::make('academicYear.name')->label('Year'),TextColumn::make('term.name')->label('Term')->placeholder('—'),TextColumn::make('section.grade.name')->label('Grade'),TextColumn::make('section.name')->label('Section'),TextColumn::make('exam_date')->date()->sortable(),TextColumn::make('status')->badge()])->recordActions([
  Action::make('generateResults')->label('Generate results')->icon(Heroicon::OutlinedCalculator)->color('warning')->visible(fn(Exam $record)=>auth()->user()?->can('Generate:Result')&&$record->status!=='published')->authorize(fn()=>auth()->user()?->can('Generate:Result')??false)->requiresConfirmation()->action(function(Exam $record):void{$actor=auth()->user();abort_unless($actor instanceof User,403);try{$count=app(GenerateExamResults::class)->generate($record,$actor);Notification::make()->success()->title('Draft results generated')->body("Generated results for {$count} students. You can review and publish them now.")->send();}catch(ValidationException $exception){Notification::make()->warning()->title('Results could not be generated')->body(collect($exception->errors())->flatten()->implode(' '))->persistent()->send();}}),
  Action::make('publishResults')->label('Publish results')->icon(Heroicon::OutlinedCheckCircle)->color('success')->visible(fn(Exam $record)=>auth()->user()?->can('Publish:Result')&&$record->status!=='published')->authorize(fn()=>auth()->user()?->can('Publish:Result')??false)->requiresConfirmation()->action(function(Exam $record):void{$actor=auth()->user();abort_unless($actor instanceof User,403);try{$count=app(GenerateExamResults::class)->publish($record,$actor);Notification::make()->success()->title('Results published')->body("Published results for {$count} students. Students and parents can now view report cards.")->send();}catch(ValidationException $exception){Notification::make()->warning()->title('Results could not be published')->body(collect($exception->errors())->flatten()->implode(' '))->persistent()->send();}}),
  \Filament\Actions\EditAction::make(),\Filament\Actions\DeleteAction::make()
 ])->toolbarActions([]); }
 public static function getPages(): array { return ['index'=>Pages\ListExams::route('/'),'create'=>Pages\CreateExam::route('/create'),'edit'=>Pages\EditExam::route('/{record}/edit')]; }
 public static function canEdit(Model $record):bool{return $record->status!=='published';}
 public static function canDelete(Model $record):bool{return $record->status!=='published';}
}
