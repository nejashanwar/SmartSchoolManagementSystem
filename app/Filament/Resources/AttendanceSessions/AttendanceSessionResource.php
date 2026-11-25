<?php

namespace App\Filament\Resources\AttendanceSessions;

use App\Models\AttendanceSession;
use App\Models\Section;
use App\Models\TeacherProfile;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use UnitEnum;

class AttendanceSessionResource extends Resource
{
    protected static ?string $model = AttendanceSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance & exams';

    protected static ?string $navigationLabel = 'Attendance sessions';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('section_id')->label('Class / section')->options(fn () => Section::query()->with(['grade', 'academicYear'])->where('is_active', true)->get()->mapWithKeys(fn ($s) => [$s->id => $s->academicYear->name.' · '.$s->grade->name.' · '.$s->name])->all())->required()->searchable()->live(),
            Select::make('teacher_profile_id')->label('Attendance teacher')->helperText('Only active teachers assigned to this section can own its attendance session.')->options(function ($get) {
                $sectionId = $get('section_id');
                if (! $sectionId) {
                    return [];
                }

                return TeacherProfile::query()->whereHas('assignments', fn ($query) => $query->where('section_id', $sectionId))->where(fn ($query) => $query->where('employment_status', 'active')->orWhereKey($get('teacher_profile_id')))->with('user')->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name])->all();
            })->required()->searchable()->rules(fn ($get) => $get('section_id') ? ['required', Rule::exists('teacher_assignments', 'teacher_profile_id')->where('section_id', $get('section_id'))] : ['required']),
            DatePicker::make('session_date')->label('Attendance date')->helperText('This is the date shown in attendance reports.')->required()->default(now()), TextInput::make('name')->label('Session name')->required()->maxLength(80)->default('Daily')->helperText('Each teacher has a separate register for their section and attendance date.')->rules(function ($get, $record) {
                if (! $get('section_id') || ! $get('session_date') || ! $get('teacher_profile_id')) {
                    return [];
                } $rule = Rule::unique('attendance_sessions', 'name')->where(fn ($query) => $query->where('teacher_profile_id', $get('teacher_profile_id'))->where('section_id', $get('section_id'))->where('session_date', $get('session_date')));
                if ($record) {
                    $rule->ignore($record->getKey());
                }

                return [$rule];
            }),
            DateTimePicker::make('starts_at')->label('Editable from')->seconds(false)->required(), DateTimePicker::make('ends_at')->label('Editable until')->seconds(false)->required()->after('starts_at'),
            Select::make('status')->options(['open' => 'Open', 'completed' => 'Completed'])->required()->default('open'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('session_date', 'desc')->columns([TextColumn::make('session_date')->date()->label('Attendance date')->sortable(), TextColumn::make('starts_at')->label('Editable from')->dateTime(), TextColumn::make('ends_at')->label('Editable until')->dateTime(), TextColumn::make('teacherProfile.user.name')->label('Attendance teacher')->searchable(), TextColumn::make('section.grade.name')->label('Grade')->searchable(), TextColumn::make('section.name')->label('Section')->searchable(), TextColumn::make('name')->label('Session')->searchable(), TextColumn::make('records_count')->counts('records')->label('Marked'), TextColumn::make('status')->badge(), TextColumn::make('creator.name')->label('Created by')->searchable()])->filters([SelectFilter::make('teacher_profile_id')->label('Teacher')->options(fn () => TeacherProfile::query()->with('user')->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name])->all())->searchable(), SelectFilter::make('section')->relationship('section', 'name')->searchable()->preload(), SelectFilter::make('status')->options(['open' => 'Open', 'completed' => 'Completed']), Filter::make('period')->schema([DatePicker::make('from')->label('From'), DatePicker::make('until')->label('Until')])->query(fn ($query, $data) => $query->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('session_date', '>=', $date))->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('session_date', '<=', $date)))])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAttendanceSessions::route('/'), 'create' => Pages\CreateAttendanceSession::route('/create'), 'edit' => Pages\EditAttendanceSession::route('/{record}/edit')];
    }
}
