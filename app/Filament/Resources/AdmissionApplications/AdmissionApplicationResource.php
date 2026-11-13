<?php

namespace App\Filament\Resources\AdmissionApplications;

use App\Filament\Resources\AdmissionApplications\Pages\ListAdmissionApplications;
use App\Filament\Resources\AdmissionApplications\Pages\ViewAdmissionApplication;
use App\Filament\Resources\AdmissionApplications\Schemas\AdmissionApplicationInfolist;
use App\Filament\Resources\AdmissionApplications\Tables\AdmissionApplicationsTable;
use App\Models\AdmissionApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdmissionApplicationResource extends Resource
{
    protected static ?string $model = AdmissionApplication::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;
    protected static string|UnitEnum|null $navigationGroup = 'Admissions';
    protected static ?string $navigationLabel = 'Applications';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema { return $schema->components([]); }
    public static function infolist(Schema $schema): Schema { return AdmissionApplicationInfolist::configure($schema); }
    public static function table(Table $table): Table { return AdmissionApplicationsTable::configure($table); }

    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmissionApplications::route('/'),
            'view' => ViewAdmissionApplication::route('/{record}'),
        ];
    }
}
