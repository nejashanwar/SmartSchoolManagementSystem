<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('starts_on')->date()->sortable(),
                TextColumn::make('ends_on')->date()->sortable(),
                TextColumn::make('admission_opens_at')->label('Registration opens')->dateTime(),
                TextColumn::make('admission_closes_at')->label('Registration closes')->dateTime(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([]);
    }
}
