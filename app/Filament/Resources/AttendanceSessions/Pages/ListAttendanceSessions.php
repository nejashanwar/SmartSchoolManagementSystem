<?php
namespace App\Filament\Resources\AttendanceSessions\Pages;
use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Models\Section;
use Filament\Resources\Pages\ListRecords;
class ListAttendanceSessions extends ListRecords { protected static string $resource=AttendanceSessionResource::class; protected function getHeaderActions(): array { return [CreateAction::make(),Action::make('attendanceReport')->label('Attendance reports')->schema([DatePicker::make('from')->required()->default(now()->startOfMonth()),DatePicker::make('until')->required()->default(now())->afterOrEqual('from'),Select::make('section_id')->label('Section (optional)')->options(fn()=>Section::query()->with('grade')->orderBy('grade_id')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->grade->name.' · '.$s->name])->all())->searchable()])->action(fn(array $data)=>redirect()->route('attendance.reports.show',$data))]; } }
