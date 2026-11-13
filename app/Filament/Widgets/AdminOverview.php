<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\AdmissionApplication;
use App\Models\Grade;
use App\Models\Student;
use App\Models\TeacherProfile;
use App\Models\AttendanceSession;
use App\Models\Result;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminOverview extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'School administration';

    protected function getStats(): array
    {
        return [
            Stat::make('User accounts', User::query()->count())
                ->description('All registered accounts')
                ->icon('heroicon-o-users'),
            Stat::make('Active accounts', User::query()->where('is_active', true)->count())
                ->description('Can sign in when assigned panel access')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Roles', Role::query()->count())
                ->description('Configured access roles')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Permissions', Permission::query()->count())
                ->description('Available access rules')
                ->icon('heroicon-o-key'),
            Stat::make('Grades', Grade::query()->where('is_active', true)->count())
                ->description('Available admission grades')
                ->icon('heroicon-o-academic-cap'),
            Stat::make('Admitted students', Student::query()->count())
                ->description('Approved student records')
                ->icon('heroicon-o-identification'),
            Stat::make('Active teachers', TeacherProfile::query()->where('employment_status', 'active')->count())
                ->description('Teacher profiles ready for assignments')
                ->icon('heroicon-o-user-group'),
            Stat::make('Applications to review', AdmissionApplication::query()
                ->whereIn('status', [AdmissionApplication::STATUS_PENDING, AdmissionApplication::STATUS_CORRECTION_REQUESTED])
                ->count())
                ->description('Pending or awaiting correction')
                ->icon('heroicon-o-inbox'),
            Stat::make('Attendance sessions today', AttendanceSession::query()->whereDate('session_date', today())->count())
                ->description('School-wide attendance monitoring')
                ->icon('heroicon-o-clipboard-document-check'),
            Stat::make('Published results', Result::query()->where('status', 'published')->count())
                ->description('Student report cards available')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
