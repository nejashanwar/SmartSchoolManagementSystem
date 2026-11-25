<?php

namespace App\Filament\Resources\ParentProfiles;

use App\Models\ParentProfile;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ParentProfileResource extends Resource
{
    protected static ?string $model = ParentProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'Parent accounts';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('phone')->tel()->maxLength(40), Select::make('students')->label('Linked students')->relationship('students', 'full_name')->multiple()->searchable()->preload()->required()]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('user.name')->label('Parent')->searchable()->sortable(), TextColumn::make('user.email')->label('Login email')->searchable(), TextColumn::make('phone')->searchable(), TextColumn::make('students_count')->counts('students')->label('Children')])->filters([
            Filter::make('has_students')->schema([Select::make('value')->label('Linked students')->options(['yes' => 'Has students', 'no' => 'No students'])])->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                'yes' => $query->has('students'),'no' => $query->doesntHave('students'),default => $query
            }),
        ])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListParentProfiles::route('/'), 'edit' => Pages\EditParentProfile::route('/{record}/edit')];
    }

    public static function canCreate(): bool
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
}
