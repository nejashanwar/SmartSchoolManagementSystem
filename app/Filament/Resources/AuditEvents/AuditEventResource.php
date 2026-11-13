<?php

namespace App\Filament\Resources\AuditEvents;

use App\Filament\Resources\AuditEvents\Pages\ListAuditEvents;
use App\Filament\Resources\AuditEvents\Pages\ViewAuditEvent;
use App\Filament\Resources\AuditEvents\Schemas\AuditEventInfolist;
use App\Filament\Resources\AuditEvents\Tables\AuditEventsTable;
use App\Models\AuditEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AuditEventResource extends Resource
{
    protected static ?string $model = AuditEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Audit log';

    protected static ?string $modelLabel = 'audit event';

    protected static ?string $pluralModelLabel = 'audit log';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditEventsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditEvents::route('/'),
            'view' => ViewAuditEvent::route('/{record}'),
        ];
    }
}
