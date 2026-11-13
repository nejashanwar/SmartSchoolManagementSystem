<?php

namespace App\Filament\Resources\AuditEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('event')->label('Action')->badge(),
            TextEntry::make('causer.name')->label('Performed by')->placeholder('System'),
            TextEntry::make('subject_type')->label('Record type')->placeholder('—'),
            TextEntry::make('subject_id')->label('Record ID')->placeholder('—'),
            TextEntry::make('properties')
                ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                ->columnSpanFull(),
            TextEntry::make('created_at')->label('Time')->dateTime(),
        ]);
    }
}
