<?php

namespace App\Filament\Resources\AdmissionApplications\Tables;

use App\Models\AdmissionApplication;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([]);
    }
}
