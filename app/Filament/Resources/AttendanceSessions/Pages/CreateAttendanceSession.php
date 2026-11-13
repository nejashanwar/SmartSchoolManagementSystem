<?php
namespace App\Filament\Resources\AttendanceSessions\Pages;
use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
class CreateAttendanceSession extends CreateRecord { protected static string $resource=AttendanceSessionResource::class; protected function mutateFormDataBeforeCreate(array $data):array{$data['created_by']=auth()->id();return $data;} protected function handleRecordCreation(array $data):Model{try{return parent::handleRecordCreation($data);}catch(UniqueConstraintViolationException $exception){throw ValidationException::withMessages(['name'=>'This teacher already has a session with this name for the selected section and date.']);}} protected function afterCreate():void{AuditEvent::record('attendance.session_created',$this->record,$this->record->only(['teacher_profile_id','section_id','session_date','name','status']));} }
