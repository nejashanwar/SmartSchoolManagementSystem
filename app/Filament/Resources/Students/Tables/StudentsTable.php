<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('admitted_at', 'desc')
            ->columns([
                TextColumn::make('admission_number')->label('Admission number')->searchable()->copyable(),
                TextColumn::make('full_name')->label('Student')->searchable()->sortable(),
                TextColumn::make('grade.name')->label('Grade')->sortable(),
                TextColumn::make('academicYear.name')->label('Academic year')->sortable(),
                TextColumn::make('section.name')->label('Section')->placeholder('Not assigned')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('admitted_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade_id')->relationship('grade', 'name')->searchable()->preload(),
                SelectFilter::make('academic_year_id')->relationship('academicYear', 'name')->label('Academic year')->searchable()->preload(),
                SelectFilter::make('section_id')->relationship('section', 'name')->searchable()->preload(),
                SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([]);
    }
}
