<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AcademicYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('starts_on')->date(),
            TextEntry::make('ends_on')->date(),
            TextEntry::make('admission_opens_at')->dateTime(),
            TextEntry::make('admission_closes_at')->dateTime(),
            TextEntry::make('is_active')->badge(),
        ]);
    }
}
