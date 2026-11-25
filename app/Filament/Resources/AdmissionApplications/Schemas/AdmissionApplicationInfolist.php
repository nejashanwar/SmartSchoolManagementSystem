<?php

namespace App\Filament\Resources\AdmissionApplications\Schemas;

use App\Models\AdmissionApplication;
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
            TextEntry::make('first_name')->label('First name')->placeholder('—'),
            TextEntry::make('father_name')->label('Father name')->placeholder('—'),
            TextEntry::make('grandfather_name')->label('Grandfather name')->placeholder('—'),
            TextEntry::make('gender')->placeholder('—'),
            TextEntry::make('date_of_birth')->date()->placeholder('—'),
            TextEntry::make('place_of_birth')->label('Place of birth')->placeholder('—'),
            TextEntry::make('nationality')->placeholder('—'),
            TextEntry::make('region')->placeholder('—'),
            TextEntry::make('zone')->placeholder('—'),
            TextEntry::make('woreda')->placeholder('—'),
            TextEntry::make('photo_path')
                ->label('Student photo')
                ->formatStateUsing(fn (?string $state): ?string => filled($state) ? 'Download photo' : null)
                ->url(fn (AdmissionApplication $record): ?string => $record->photo_path
                    ? route('admin.admission-applications.download', ['application' => $record, 'field' => 'photo_path'])
                    : null)
                ->openUrlInNewTab()
                ->placeholder('—'),
            TextEntry::make('zone_woreda')->label('Zone / woreda')->placeholder('—'),
            TextEntry::make('kebele')->placeholder('—'),
            TextEntry::make('address')->label('Student address')->placeholder('—')->columnSpanFull(),
            TextEntry::make('grade.name')->label('Applying grade'),
            TextEntry::make('academicYear.name')->label('Academic year'),
            TextEntry::make('guardian_name')->label('Parent/guardian'),
            TextEntry::make('guardian_phone')->label('Guardian phone')->copyable(),
            TextEntry::make('guardian_email')->label('Guardian email')->placeholder('—'),
            TextEntry::make('guardian_relationship')->label('Guardian relationship')->placeholder('—'),
            TextEntry::make('guardian_occupation')->label('Guardian occupation')->placeholder('—'),
            TextEntry::make('guardian_address')->label('Guardian address')->placeholder('—')->columnSpanFull(),
            TextEntry::make('emergency_contact_name')->label('Emergency contact')->placeholder('—'),
            TextEntry::make('emergency_contact_phone')->label('Emergency contact phone')->placeholder('—'),
            TextEntry::make('previous_school')->label('Previous school')->placeholder('—'),
            TextEntry::make('previous_grade')->label('Previous grade')->placeholder('—'),
            TextEntry::make('transfer_reason')->label('Transfer reason')->placeholder('—')->columnSpanFull(),
            TextEntry::make('birth_certificate_path')
                ->label('Birth certificate')
                ->formatStateUsing(fn (?string $state): ?string => filled($state) ? 'Download certificate' : null)
                ->url(fn (AdmissionApplication $record): ?string => $record->birth_certificate_path
                    ? route('admin.admission-applications.download', ['application' => $record, 'field' => 'birth_certificate_path'])
                    : null)
                ->openUrlInNewTab()
                ->placeholder('—'),
            TextEntry::make('previous_transcript_path')
                ->label('Previous transcript')
                ->formatStateUsing(fn (?string $state): ?string => filled($state) ? 'Download transcript' : null)
                ->url(fn (AdmissionApplication $record): ?string => $record->previous_transcript_path
                    ? route('admin.admission-applications.download', ['application' => $record, 'field' => 'previous_transcript_path'])
                    : null)
                ->openUrlInNewTab()
                ->placeholder('—'),
            TextEntry::make('health_notes')->label('Health notes')->placeholder('—')->columnSpanFull(),
            TextEntry::make('submitted_at')->dateTime(),
            TextEntry::make('reviewer.name')->label('Reviewed by')->placeholder('—'),
            TextEntry::make('reviewed_at')->dateTime()->placeholder('—'),
            TextEntry::make('review_reason')->label('Review reason')->columnSpanFull()->placeholder('—'),
            TextEntry::make('student.admission_number')->label('Admission number')->placeholder('Not admitted'),
            TextEntry::make('student.user.username')->label('Student login')->placeholder('—'),
        ]);
    }
}
