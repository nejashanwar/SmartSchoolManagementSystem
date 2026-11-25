<?php

namespace App\Filament\Resources\Subjects;

use App\Models\Subject;
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

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Academic setup';

    protected static ?string $navigationLabel = 'Subjects';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->required()->maxLength(120)->unique(ignoreRecord: true), TextInput::make('code')->maxLength(30)->unique(ignoreRecord: true), Toggle::make('is_active')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('code')->searchable(), TextColumn::make('teacher_assignments_count')->counts('teacherAssignments')->label('Assignments'), IconColumn::make('is_active')->boolean()])->filters([SelectFilter::make('is_active')->label('Status')->options(['1' => 'Active', '0' => 'Inactive'])])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSubjects::route('/'), 
        'create' => Pages\CreateSubject::route('/create'), 
        'edit' => Pages\EditSubject::route('/{record}/edit')];
    }
}
