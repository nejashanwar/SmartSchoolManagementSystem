<?php

namespace App\Filament\Resources\Results;

use App\Models\Result;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance & exams';

    protected static ?string $navigationLabel = 'Results & performance';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['exam.section.grade', 'exam.examType', 'student']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('exam_id')
            ->columns([
                TextColumn::make('exam.name')->label('Examination')->searchable()->sortable(),
                TextColumn::make('student.admission_number')->label('Student ID')->searchable(),
                TextColumn::make('student.full_name')->label('Student')->searchable()->sortable(),
                TextColumn::make('exam.section.grade.name')->label('Grade'),
                TextColumn::make('exam.section.name')->label('Section'),
                TextColumn::make('average_percentage')
                    ->label('Average')
                    ->suffix('%')
                    ->sortable()
                    ->summarize(Average::make()->label('Class average')),
                TextColumn::make('section_rank')
                    ->label('Rank')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('total_marks')->label('Total'),
                TextColumn::make('maximum_marks')->label('Out of'),
                TextColumn::make('grade_label')->label('Grade')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('published_at')->dateTime()->label('Published'),
            ])
            ->filters([
                SelectFilter::make('exam_id')->relationship('exam', 'name')->searchable()->preload(),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListResults::route('/')];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
