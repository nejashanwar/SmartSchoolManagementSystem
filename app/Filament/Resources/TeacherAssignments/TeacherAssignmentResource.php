<?php
namespace App\Filament\Resources\TeacherAssignments;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\TeacherProfile;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
class TeacherAssignmentResource extends Resource {
 protected static ?string $model=TeacherAssignment::class; protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedClipboardDocumentList; protected static string|UnitEnum|null $navigationGroup='Staff'; protected static ?string $navigationLabel='Teacher assignments'; protected static ?int $navigationSort=2;
 public static function form(Schema $schema): Schema { return $schema->components([
  Select::make('teacher_profile_id')->label('Teacher')->options(fn()=>TeacherProfile::query()->with('user')->where('employment_status','active')->get()->mapWithKeys(fn($t)=>[$t->id=>$t->user->name])->all())->required()->searchable(),
  Select::make('section_id')->label('Section')->options(fn()=>Section::query()->with(['grade','academicYear'])->where('is_active',true)->get()->mapWithKeys(fn($s)=>[$s->id=>$s->academicYear->name.' · '.$s->grade->name.' · '.$s->name])->all())->required()->searchable(),
  Select::make('subject_id')->label('Subject')->options(fn()=>Subject::query()->where('is_active',true)->orderBy('name')->pluck('name','id'))->required()->searchable(),
 ]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('teacherProfile.user.name')->label('Teacher')->searchable(), TextColumn::make('section.academicYear.name')->label('Year'), TextColumn::make('section.grade.name')->label('Grade'), TextColumn::make('section.name')->label('Section'), TextColumn::make('subject.name')->label('Subject')->searchable()])->recordActions([\Filament\Actions\EditAction::make(),\Filament\Actions\DeleteAction::make()])->toolbarActions([]); }
 public static function getPages(): array { return ['index'=>Pages\ListTeacherAssignments::route('/'),'create'=>Pages\CreateTeacherAssignment::route('/create'),'edit'=>Pages\EditTeacherAssignment::route('/{record}/edit')]; }
}
