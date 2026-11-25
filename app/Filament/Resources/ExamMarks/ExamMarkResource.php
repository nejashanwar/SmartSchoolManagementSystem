<?php

namespace App\Filament\Resources\ExamMarks;

use App\Models\ExamAssessmentComponent;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use UnitEnum;

class ExamMarkResource extends Resource
{
    protected static ?string $model = ExamMark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance & exams';

    protected static ?string $navigationLabel = 'Marks';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('assessment_component_id')->label('Exam, subject and assessment')->options(fn () => ExamAssessmentComponent::query()->with(['examSubject.exam.section.grade', 'examSubject.subject'])->whereHas('examSubject.exam', fn ($q) => $q->whereNotIn('status', ['completed', 'published']))->get()->mapWithKeys(fn ($component) => [$component->id => $component->examSubject->exam->name.' · '.$component->examSubject->exam->section->grade->name.' '.$component->examSubject->exam->section->name.' · '.$component->examSubject->subject->name.' · '.$component->name.' ('.$component->weight.'%, /'.$component->max_marks.')'])->all())->required()->live()->searchable()->afterStateUpdated(function ($state, $set) {
                $component = ExamAssessmentComponent::find($state);
                if ($component) {
                    $set('exam_subject_id', $component->exam_subject_id);
                }
            }),
            Hidden::make('exam_subject_id')->required(),
            Select::make('student_id')->label('Student')->options(function ($get) {
                $component = ExamAssessmentComponent::with('examSubject.exam')->find($get('assessment_component_id'));

                return $component ? Student::query()->where('section_id', $component->examSubject->exam->section_id)->orderBy('full_name')->pluck('full_name', 'id') : [];
            })->required()->searchable()->rules(function ($get, $record = null) {
                $component = ExamAssessmentComponent::with('examSubject.exam')->find($get('assessment_component_id'));
                if (! $component) {
                    return ['required'];
                }$unique = Rule::unique('exam_marks', 'student_id')->where('assessment_component_id', $component->id);
                if ($record) {
                    $unique->ignore($record->getKey());
                }

                return [Rule::exists('students', 'id')->where('section_id', $component->examSubject->exam->section_id), $unique];
            }),
            TextInput::make('marks_obtained')->label('Marks obtained')->numeric()->required()->minValue(0)->maxValue(fn ($get) => ExamAssessmentComponent::find($get('assessment_component_id'))?->max_marks ?? 999999.99),
            Select::make('status')->label('Review status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'verified' => 'Verified'])->default('draft')->required()->helperText('Only verified marks in an approved subject register can be used to generate results.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('examSubject.exam.name')->label('Examination')->searchable(), TextColumn::make('examSubject.exam.section.name')->label('Section')->searchable(), TextColumn::make('examSubject.subject.name')->label('Subject')->searchable(), TextColumn::make('assessmentComponent.name')->label('Assessment')->searchable(), TextColumn::make('student.full_name')->label('Student')->searchable(), TextColumn::make('marks_obtained')->label('Mark'), TextColumn::make('assessmentComponent.max_marks')->label('Out of'), TextColumn::make('assessmentComponent.weight')->label('Weight')->suffix('%'), TextColumn::make('status')->badge(), TextColumn::make('examiner.name')->label('Entered by')->searchable()])->filters([
            SelectFilter::make('status')->label('Review status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'verified' => 'Verified']),
            SelectFilter::make('exam_subject_id')->label('Examination subject')->options(fn () => ExamSubject::query()->with(['exam', 'subject'])->get()->mapWithKeys(fn ($examSubject) => [$examSubject->id => $examSubject->exam->name.' · '.$examSubject->subject->name])->all())->searchable()->preload(),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListExamMarks::route('/'), 'create' => Pages\CreateExamMark::route('/create'), 'edit' => Pages\EditExamMark::route('/{record}/edit')];
    }

    public static function canEdit(Model $record): bool
    {
        return ! in_array($record->examSubject->exam->status, ['completed', 'published'], true);
    }

    public static function canDelete(Model $record): bool
    {
        return ! in_array($record->examSubject->exam->status, ['completed', 'published'], true);
    }
}
