<?php

namespace App\Filament\Resources\AttendanceRecords;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use UnitEnum;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance & exams';

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('attendance_session_id')->label('Attendance session')->options(fn () => AttendanceSession::query()->with(['teacherProfile.user', 'section.grade'])->orderByDesc('session_date')->get()->mapWithKeys(fn ($s) => [$s->id => $s->session_date->format('M j, Y').' · '.$s->teacherProfile?->user?->name.' · '.$s->section->grade->name.' '.$s->section->name.' · '.$s->name])->all())->required()->live()->searchable(),
            Select::make('student_id')->label('Student')->options(function ($get) {
                $session = AttendanceSession::find($get('attendance_session_id'));

                return $session ? Student::query()->where('section_id', $session->section_id)->orderBy('full_name')->pluck('full_name', 'id') : [];
            })->required()->searchable()->rules(function ($get, $record) {
                $session = AttendanceSession::find($get('attendance_session_id'));

                return $session ? [Rule::exists('students', 'id')->where('section_id', $session->section_id), Rule::unique('attendance_records', 'student_id')->where('attendance_session_id', $session->id)->ignore($record?->id)] : ['required'];
            }),
            Select::make('status')->options(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'])->required(),
            Textarea::make('note')->maxLength(500)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('attendance_date', 'desc')->columns([TextColumn::make('session.session_date')->label('Date')->date()->sortable(), TextColumn::make('session.name')->label('Session')->searchable(), TextColumn::make('section.grade.name')->label('Grade')->searchable(), TextColumn::make('section.name')->label('Section')->searchable(), TextColumn::make('student.full_name')->label('Student')->searchable(), TextColumn::make('status')->badge(), TextColumn::make('recorder.name')->label('Recorded by')->searchable()])->filters([
            SelectFilter::make('attendance_session_id')->label('Session')->relationship('session', 'name')->searchable()->preload(),
            SelectFilter::make('status')->options(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused']),
            SelectFilter::make('section_id')->relationship('section', 'name')->searchable()->preload(),
            Filter::make('attendance_date')->schema([DatePicker::make('from')->label('From'), DatePicker::make('until')->label('Until')])->query(fn ($query, $data) => $query->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAttendanceRecords::route('/'), 'create' => Pages\CreateAttendanceRecord::route('/create'), 'edit' => Pages\EditAttendanceRecord::route('/{record}/edit')];
    }
}
