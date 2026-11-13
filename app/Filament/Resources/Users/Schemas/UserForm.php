<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Full name')
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('name'),
                TextInput::make('email')
                    ->email()
                    ->required(fn (Get $get): bool => ! Role::query()
                        ->whereKey((array) $get('roles'))
                        ->where('name', 'STUDENT')
                        ->exists())
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->autocomplete('email'),
                TextInput::make('username')
                    ->maxLength(80)
                    ->unique(ignoreRecord: true)
                    ->autocomplete('username'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->confirmed()
                    ->minLength(12)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->autocomplete('new-password'),
                Select::make('roles')
                    ->label('Roles')
                    ->disabled(fn ($record): bool => $record?->is(auth()->user()) ?? false)
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => auth()->user()?->hasRole('super_admin')
                            ? $query
                            : $query->where('name', '!=', 'super_admin'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('TEACHER creates a teacher profile; PARENT enables the parent portal. Student accounts come from approved admissions.')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Account active')
                    ->default(true)
                    ->disabled(fn ($record): bool => $record?->is(auth()->user()) ?? false)
                    ->helperText('Inactive users cannot sign in to the admin panel.'),
            ]);
    }
}
