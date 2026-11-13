<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(80)->unique(ignoreRecord: true),
            DatePicker::make('starts_on')->label('Academic year starts on'),
            DatePicker::make('ends_on')->label('Academic year ends on'),
            DateTimePicker::make('admission_opens_at')->label('Admission registration opens'),
            DateTimePicker::make('admission_closes_at')
                ->label('Admission registration closes')
                ->after('admission_opens_at'),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
