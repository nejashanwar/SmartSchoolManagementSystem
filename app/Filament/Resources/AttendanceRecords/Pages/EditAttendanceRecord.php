<?php
namespace App\Filament\Resources\AttendanceRecords\Pages;
use App\Filament\Resources\AttendanceRecords\AttendanceRecordResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
class EditAttendanceRecord extends EditRecord { protected static string $resource=AttendanceRecordResource::class; protected function mutateFormDataBeforeSave(array $data):array{$session=\App\Models\AttendanceSession::findOrFail($data['attendance_session_id']);$data['section_id']=$session->section_id;$data['attendance_date']=$session->session_date;return $data;} protected function afterSave(): void { AuditEvent::record('attendance.updated',$this->record,$this->record->only(['attendance_session_id','student_id','section_id','attendance_date','status'])); } }
