<?php

namespace App\Filament\Resources\AdmissionApplications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdmissionApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('reference_number')->label('Reference')->copyable(),
            TextEntry::make('status')->badge(),
            TextEntry::make('student_name')->label('Student full name'),
            TextEntry::make('grade.name')->label('Applying grade'),
            TextEntry::make('academicYear.name')->label('Academic year'),
            TextEntry::make('guardian_name')->label('Parent/guardian'),
            TextEntry::make('guardian_phone')->label('Guardian phone')->copyable(),
            TextEntry::make('guardian_email')->label('Guardian email')->placeholder('—'),
            TextEntry::make('previous_school')->label('Previous school')->placeholder('—'),
            TextEntry::make('submitted_at')->dateTime(),
            TextEntry::make('reviewer.name')->label('Reviewed by')->placeholder('—'),
            TextEntry::make('reviewed_at')->dateTime()->placeholder('—'),
            TextEntry::make('review_reason')->label('Review reason')->columnSpanFull()->placeholder('—'),
            TextEntry::make('student.admission_number')->label('Admission number')->placeholder('Not admitted'),
            TextEntry::make('student.user.username')->label('Student login')->placeholder('—'),
        ]);
    }
}
