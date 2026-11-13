<?php
namespace App\Filament\Resources\AttendanceRecords\Pages;
use App\Filament\Resources\AttendanceRecords\AttendanceRecordResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;
class CreateAttendanceRecord extends CreateRecord { protected static string $resource=AttendanceRecordResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $session=\App\Models\AttendanceSession::findOrFail($data['attendance_session_id']); $data['section_id']=$session->section_id; $data['attendance_date']=$session->session_date; $data['recorded_by']=auth()->id(); return $data; } protected function afterCreate(): void { AuditEvent::record('attendance.recorded',$this->record,$this->record->only(['attendance_session_id','student_id','section_id','attendance_date','status'])); } }
