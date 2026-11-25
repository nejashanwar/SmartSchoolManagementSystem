<?php

namespace App\Filament\Resources\GradeScales;

use App\Models\GradeScale;
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

class GradeScaleResource extends Resource
{
    protected static ?string $model = GradeScale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Academic setup';

    protected static ?string $navigationLabel = 'Grade scale';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('label')->label('Grade label')->required()->maxLength(20)->unique(ignoreRecord: true), TextInput::make('min_percentage')->numeric()->required()->minValue(0)->maxValue(100), TextInput::make('max_percentage')->numeric()->required()->minValue(0)->maxValue(100)->rules(fn ($get, $record) => [function ($attribute, $value, $fail) use ($get, $record) {
            $min = (float) $get('min_percentage');
            $max = (float) $value;
            if ($max < $min) {
                $fail('Maximum percentage must be at least the minimum percentage.');

                return;
            }$bands = GradeScale::query()->where('is_active', true)->where('min_percentage', '<=', $max)->where('max_percentage', '>=', $min);
            if ($record) {
                $bands->whereKeyNot($record->getKey());
            }if ($bands->exists()) {
                $fail('This percentage range overlaps another active grade band.');
            }
        }]), TextInput::make('sort_order')->numeric()->integer()->required()->minValue(1)->unique(ignoreRecord: true), Toggle::make('is_active')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([TextColumn::make('sort_order')->label('#'), TextColumn::make('label')->label('Grade')->searchable(), TextColumn::make('min_percentage'), TextColumn::make('max_percentage'), IconColumn::make('is_active')->boolean()])->filters([SelectFilter::make('is_active')->label('Status')->options(['1' => 'Active', '0' => 'Inactive'])])->recordActions([EditAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGradeScales::route('/'), 'create' => Pages\CreateGradeScale::route('/create'), 'edit' => Pages\EditGradeScale::route('/{record}/edit')];
    }
}
