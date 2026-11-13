<?php

namespace App\Filament\Resources\TeacherProfiles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeacherProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('phone')->tel()->maxLength(40),
            DatePicker::make('joined_on')->label('Date joined'),
            Select::make('employment_status')->required()->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]),
        ]);
    }
}
