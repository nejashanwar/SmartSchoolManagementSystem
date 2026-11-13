<?php
namespace App\Filament\Resources\Students\Pages;
use App\Filament\Resources\Students\StudentResource;
use App\Models\AuditEvent;
use Filament\Resources\Pages\EditRecord;
class EditStudent extends EditRecord { protected static string $resource = StudentResource::class; protected function afterSave(): void { AuditEvent::record('student.section_assigned', $this->record, ['section_id' => $this->record->section_id]); } }
