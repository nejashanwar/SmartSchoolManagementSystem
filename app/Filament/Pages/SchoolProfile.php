<?php

namespace App\Filament\Pages;

use App\Models\SchoolSetting;
use App\Models\AuditEvent;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SchoolProfile extends Page
{
    use HasPageShield;

    protected string $view = 'filament.pages.school-profile';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'School profile';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SchoolSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('School name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contact_email')
                    ->label('Contact email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(40),
                Textarea::make('address')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model(SchoolSetting::class);
    }

    public function save(): void
    {
        $settings = SchoolSetting::current();
        $before = $settings->only(['name', 'contact_email', 'phone', 'address']);
        $settings->update($this->form->getState());

        AuditEvent::record('school_profile.updated', $settings, [
            'before' => $before,
            'after' => $settings->only(['name', 'contact_email', 'phone', 'address']),
        ]);

        Notification::make()
            ->success()
            ->title('School profile saved')
            ->send();
    }
}
