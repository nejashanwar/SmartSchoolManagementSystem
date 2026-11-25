<?php

namespace App\Filament\Exports;

use App\Models\AdmissionApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class AdmissionApplicationExporter extends Exporter
{
    protected static ?string $model = AdmissionApplication::class;

    public static function getColumns(): array
    {
        return [
            self::text('reference_number', 'Reference'),
            self::text('submitted_at', 'Submitted at'),
            self::text('status', 'Status'),
            self::text('student_name', 'Student full name'),
            self::text('first_name', 'First name'),
            self::text('father_name', 'Father name'),
            self::text('grandfather_name', 'Grandfather name'),
            self::text('gender', 'Gender'),
            self::text('date_of_birth', 'Date of birth'),
            self::text('place_of_birth', 'Place of birth'),
            self::text('nationality', 'Nationality'),
            self::text('region', 'Region'),
            self::text('zone', 'Zone'),
            self::text('woreda', 'Woreda'),
            self::text('zone_woreda', 'Legacy zone / woreda'),
            self::text('kebele', 'Kebele'),
            self::text('address', 'Student address'),
            self::text('grade.name', 'Applying grade'),
            self::text('academicYear.name', 'Academic year'),
            self::text('guardian_name', 'Parent / guardian'),
            self::text('guardian_phone', 'Guardian phone'),
            self::text('guardian_email', 'Guardian email'),
            self::text('guardian_relationship', 'Guardian relationship'),
            self::text('guardian_occupation', 'Guardian occupation'),
            self::text('guardian_address', 'Guardian address'),
            self::text('emergency_contact_name', 'Emergency contact'),
            self::text('emergency_contact_phone', 'Emergency contact phone'),
            self::text('previous_school', 'Previous school'),
            self::text('previous_grade', 'Previous grade'),
            self::text('transfer_reason', 'Transfer reason'),
            self::text('health_notes', 'Health notes'),
            self::attachment('photo_path', 'Photo attached'),
            self::attachment('birth_certificate_path', 'Birth certificate attached'),
            self::attachment('previous_transcript_path', 'Previous transcript attached'),
            self::text('reviewer.name', 'Reviewed by'),
            self::text('reviewed_at', 'Reviewed at'),
            self::text('review_reason', 'Review reason'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your admission application export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    private static function text(string $column, string $label): ExportColumn
    {
        return ExportColumn::make($column)
            ->label($label)
            ->preventFormulaInjection();
    }

    private static function attachment(string $column, string $label): ExportColumn
    {
        return ExportColumn::make($column)
            ->label($label)
            ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Available' : 'Not provided');
    }
}
