<?php

namespace App\Filament\Resources\ExamTypes;

use App\Models\ExamType;
use BackedEnum;
use Filament\Actions\EditAction;
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

class ExamTypeResource extends Resource
{
    protected static ?string $model = ExamType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance & exams';

    protected static ?string $navigationLabel = 'Examination types';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->required()->maxLength(80)->unique(ignoreRecord: true), Toggle::make('is_active')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('exams_count')->counts('exams')->label('Examinations'), IconColumn::make('is_active')->boolean()])->filters([SelectFilter::make('is_active')->label('Status')->options(['1' => 'Active', '0' => 'Inactive'])])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListExamTypes::route('/'), 'create' => Pages\CreateExamType::route('/create'), 'edit' => Pages\EditExamType::route('/{record}/edit')];
    }
}
