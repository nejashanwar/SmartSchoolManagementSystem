<?php

namespace App\Filament\Resources\AuditEvents\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event')->label('Action')->badge()->searchable(),
                TextColumn::make('causer.name')->label('Performed by')->placeholder('System')->searchable(),
                TextColumn::make('subject_type')->label('Record type')->toggleable(),
                TextColumn::make('subject_id')->label('Record ID')->toggleable(),
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
