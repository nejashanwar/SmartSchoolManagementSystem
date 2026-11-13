<?php

namespace App\Filament\Resources\TeacherProfiles\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeacherProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Teacher')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('joined_on')->date()->sortable(),
                TextColumn::make('employment_status')->badge(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([]);
    }
}
