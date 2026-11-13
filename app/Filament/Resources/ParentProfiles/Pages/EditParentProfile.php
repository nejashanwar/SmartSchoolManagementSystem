<?php
namespace App\Filament\Resources\ParentProfiles\Pages;
use App\Filament\Resources\ParentProfiles\ParentProfileResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
class EditParentProfile extends EditRecord { protected static string $resource=ParentProfileResource::class; protected function afterSave():void{AuditEvent::record('parent.profile_updated',$this->record,['student_ids'=>$this->record->students()->pluck('students.id')->all()]);} }
