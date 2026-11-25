<?php

namespace App\Filament\Resources\Terms;

use App\Models\Term;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TermResource extends Resource
{
    protected static ?string $model = Term::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Academic setup';

    protected static ?string $navigationLabel = 'Terms';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Select::make('academic_year_id')->relationship('academicYear', 'name')->required()->searchable()->preload(), TextInput::make('name')->required()->maxLength(80)->helperText('Use Semester 1 or Semester 2.'), Select::make('sort_order')->label('Semester order')->options([1 => 'Semester 1', 2 => 'Semester 2'])->required(), DatePicker::make('starts_on'), DatePicker::make('ends_on')->after('starts_on'), Toggle::make('is_active')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([TextColumn::make('academicYear.name')->label('Academic year')->sortable()->searchable(), TextColumn::make('name')->searchable(), TextColumn::make('sort_order')->label('Order'), TextColumn::make('starts_on')->date(), TextColumn::make('ends_on')->date(), IconColumn::make('is_active')->boolean()])->filters([
            SelectFilter::make('academic_year_id')->relationship('academicYear', 'name')->label('Academic year')->searchable()->preload(),
            SelectFilter::make('is_active')->label('Status')->options(['1' => 'Active', '0' => 'Inactive']),
        ])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTerms::route('/'), 'create' => Pages\CreateTerm::route('/create'), 'edit' => Pages\EditTerm::route('/{record}/edit')];
    }
}
