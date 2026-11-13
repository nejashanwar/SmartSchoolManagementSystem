<?php

namespace App\Filament\Resources\AuditEvents\Pages;

use App\Filament\Resources\AuditEvents\AuditEventResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditEvent extends ViewRecord
{
    protected static string $resource = AuditEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to audit log')
                ->url(AuditEventResource::getUrl('index')),
        ];
    }
}
