<?php

namespace App\Filament\Resources\TeacherProfiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label('Teacher'),
            TextEntry::make('user.email')->label('Email')->copyable(),
            TextEntry::make('phone')->placeholder('—')->copyable(),
            TextEntry::make('joined_on')->date()->placeholder('—'),
            TextEntry::make('employment_status')->badge(),
        ]);
    }
}
