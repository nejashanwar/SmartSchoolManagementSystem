<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(80)->unique(ignoreRecord: true),
            TextInput::make('sort_order')->numeric()->integer()->required()->minValue(1)->unique(ignoreRecord: true),
            Toggle::make('is_active')->label('Available for admissions')->default(true),
        ]);
    }
}
