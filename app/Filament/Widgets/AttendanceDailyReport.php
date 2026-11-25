<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceSession;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AttendanceDailyReport extends TableWidget
{
    protected static ?string $heading = "Today's attendance";

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('ViewAny:AttendanceSession');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AttendanceSession::query()->with(['teacherProfile.user', 'section.grade', 'section.students', 'records'])->whereDate('session_date', today())->orderBy('section_id')->orderBy('teacher_profile_id')->orderBy('name'))
            ->columns([
                TextColumn::make('section.grade.name')->label('Grade')->searchable(),
                TextColumn::make('section.name')->label('Section')->searchable(),
                TextColumn::make('teacherProfile.user.name')->label('Teacher')->searchable(),
                TextColumn::make('name')->label('Session')->searchable(),
                TextColumn::make('completion')->label('Marked')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['marked'].' / '.$record->attendanceReportSummary()['total']),
                TextColumn::make('present_count')->label('Present')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['counts']['present']),
                TextColumn::make('absent_count')->label('Absent')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['counts']['absent']),
                TextColumn::make('late_count')->label('Late')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['counts']['late']),
                TextColumn::make('excused_count')->label('Excused')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['counts']['excused']),
                TextColumn::make('unmarked_count')->label('Not recorded')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['unmarked']),
                TextColumn::make('progress')->label('Complete')->state(fn (AttendanceSession $record) => $record->attendanceReportSummary()['completion'])->suffix('%'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('section_id')->relationship('section', 'name')->searchable()->preload(),
                SelectFilter::make('teacher_profile_id')->label('Teacher')->options(fn () => TeacherProfile::query()->with('user')->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name])->all())->searchable(),
            ])
            ->headerActions([Action::make('reports')->label('Reports and downloads')->url(route('attendance.reports.show'))])
            ->paginated(false);
    }
}
