<?php
namespace App\Filament\Resources\Sections;
use App\Models\Section;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
class SectionResource extends Resource {
 protected static ?string $model=Section::class; protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedRectangleStack; protected static string|UnitEnum|null $navigationGroup='Academic setup'; protected static ?string $navigationLabel='Sections'; protected static ?int $navigationSort=4;
 public static function form(Schema $schema): Schema { return $schema->components([Select::make('academic_year_id')->relationship('academicYear','name')->required()->searchable()->preload(), Select::make('grade_id')->relationship('grade','name')->required()->searchable()->preload(), TextInput::make('name')->required()->maxLength(80), TextInput::make('capacity')->numeric()->integer()->minValue(1), Toggle::make('is_active')->default(true)]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('academicYear.name')->label('Academic year')->sortable(), TextColumn::make('grade.name')->label('Grade')->sortable(), TextColumn::make('name')->searchable(), TextColumn::make('capacity')->placeholder('No limit'), TextColumn::make('students_count')->counts('students')->label('Students'), IconColumn::make('is_active')->boolean()])->recordActions([\Filament\Actions\EditAction::make()])->toolbarActions([]); }
 public static function getPages(): array { return ['index'=>Pages\ListSections::route('/'),'create'=>Pages\CreateSection::route('/create'),'edit'=>Pages\EditSection::route('/{record}/edit')]; }
}
