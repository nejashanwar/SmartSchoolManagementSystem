<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('admission_number')->label('Admission number')->copyable(),
            TextEntry::make('full_name')->label('Student name'),
            TextEntry::make('user.username')->label('Login username')->copyable(),
            TextEntry::make('grade.name')->label('Grade'),
            TextEntry::make('academicYear.name')->label('Academic year'),
            TextEntry::make('section.name')->label('Section')->placeholder('Not assigned'),
            TextEntry::make('application.reference_number')->label('Application reference'),
            TextEntry::make('application.guardian_name')->label('Parent/guardian'),
            TextEntry::make('application.guardian_phone')->label('Guardian phone')->copyable(),
            TextEntry::make('status')->badge(),
            TextEntry::make('admitted_at')->dateTime(),
        ]);
    }
}
