<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Full name'),
                TextEntry::make('email')
                    ->copyable(),
                TextEntry::make('username')->placeholder('—')->copyable(),
                TextEntry::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', '),
                TextEntry::make('is_active')
                    ->label('Account status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ]);
    }
}
