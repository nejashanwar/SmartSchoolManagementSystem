<?php

namespace App\Filament\Resources\AuditEvents\Tables;

use App\Models\AuditEvent;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('subject_type')->label('Record type')->searchable()->toggleable(),
                TextColumn::make('subject_id')->label('Record ID')->toggleable(),
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Action')
                    ->options(fn (): array => AuditEvent::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all())
                    ->searchable(),
                SelectFilter::make('causer_id')
                    ->label('Performed by')
                    ->relationship('causer', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
