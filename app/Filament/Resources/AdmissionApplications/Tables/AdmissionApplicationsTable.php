<?php

namespace App\Filament\Resources\AdmissionApplications\Tables;

use App\Filament\Exports\AdmissionApplicationExporter;
use App\Models\AdmissionApplication;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class AdmissionApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')->label('Reference')->searchable()->copyable(),
                TextColumn::make('student_name')->searchable()->sortable(),
                TextColumn::make('grade.name')->label('Grade')->sortable(),
                TextColumn::make('academicYear.name')->label('Academic year')->sortable(),
                TextColumn::make('guardian_name')->label('Parent/guardian')->searchable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    AdmissionApplication::STATUS_APPROVED => 'success',
                    AdmissionApplication::STATUS_REJECTED => 'danger',
                    AdmissionApplication::STATUS_CORRECTION_REQUESTED => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    AdmissionApplication::STATUS_PENDING => 'Pending',
                    AdmissionApplication::STATUS_CORRECTION_REQUESTED => 'Correction requested',
                    AdmissionApplication::STATUS_APPROVED => 'Approved',
                    AdmissionApplication::STATUS_REJECTED => 'Rejected',
                ]),
                SelectFilter::make('grade')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->label('Academic year')
                    ->searchable()
                    ->preload(),
                Filter::make('submitted_at')
                    ->schema([
                        DatePicker::make('from')->label('Submitted from'),
                        DatePicker::make('until')->label('Submitted until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('submitted_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('submitted_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Export filtered applications')
                    ->exporter(AdmissionApplicationExporter::class)
                    ->formats([ExportFormat::Csv, ExportFormat::Xlsx])
                    ->authorize('export', AdmissionApplication::class),
                Action::make('printReport')
                    ->label('Print report / Save as PDF')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->authorize('export', AdmissionApplication::class)
                    ->url(fn (Component $livewire): string => route('admin.admission-applications.report', [
                        'filters' => $livewire->tableFilters ?? [],
                        'search' => $livewire->getTableSearch(),
                    ], false))
                    ->openUrlInNewTab(),
            ]);
    }
}
