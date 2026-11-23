<?php
namespace App\Filament\Resources\AttendanceSessions\Pages;
use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
class EditAttendanceSession extends EditRecord { protected static string $resource=AttendanceSessionResource::class; protected function handleRecordUpdate(Model $record,array $data):Model{try{return parent::handleRecordUpdate($record,$data);}catch(UniqueConstraintViolationException $exception){throw ValidationException::withMessages(['name'=>'This teacher already has a session with this name for the selected section and date.']);}} protected function afterSave():void{AuditEvent::record('attendance.session_updated',$this->record,$this->record->only(['teacher_profile_id','section_id','session_date','name','status']));} }
