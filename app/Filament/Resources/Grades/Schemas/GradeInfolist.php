<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GradeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('sort_order')->label('Display order'),
            TextEntry::make('is_active')->label('Available for admissions')->badge(),
        ]);
    }
}
