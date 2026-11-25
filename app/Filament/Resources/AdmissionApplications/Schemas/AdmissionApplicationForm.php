<?php

namespace App\Filament\Resources\AdmissionApplications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use ResourceBundle;

class AdmissionApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        $countryOptions = self::countryOptions();

        return $schema->components([
            TextInput::make('student_name')
                ->label('Student full name')
                ->required()
                ->maxLength(255),
            TextInput::make('first_name')
                ->label('First name')
                ->rules(['required_with:father_name'])
                ->maxLength(100),
            TextInput::make('father_name')
                ->label('Father name')
                ->rules(['required_with:first_name'])
                ->maxLength(100),
            TextInput::make('grandfather_name')
                ->label('Grandfather name')
                ->maxLength(100),
            Select::make('gender')
                ->options(['male' => 'Male', 'female' => 'Female'])
                ->nullable(),
            DatePicker::make('date_of_birth')
                ->label('Date of birth')
                ->nullable(),
            Select::make('nationality')
                ->options($countryOptions)
                ->rules([Rule::in(array_keys($countryOptions))])
                ->searchable()
                ->live()
                ->nullable()
                ->afterStateUpdated(function (Set $set, ?string $state): void {
                    if ($state !== 'Ethiopia') {
                        $set('region', null);
                        $set('zone', null);
                        $set('woreda', null);
                        $set('kebele', null);
                    }
                }),
            Select::make('region')
                ->options(self::regionOptions())
                ->searchable()
                ->live()
                ->visible(fn (Get $get): bool => $get('nationality') === 'Ethiopia')
                ->required(fn (Get $get): bool => $get('nationality') === 'Ethiopia')
                ->afterStateUpdated(function (Set $set): void {
                    $set('zone', null);
                    $set('woreda', null);
                }),
            Select::make('zone')
                ->options(fn (Get $get): array => self::zoneOptions($get('region')))
                ->searchable()
                ->live()
                ->visible(fn (Get $get): bool => $get('nationality') === 'Ethiopia')
                ->required(fn (Get $get): bool => $get('nationality') === 'Ethiopia')
                ->afterStateUpdated(fn (Set $set) => $set('woreda', null)),
            Select::make('woreda')
                ->options(fn (Get $get): array => self::woredaOptions($get('region'), $get('zone')))
                ->searchable()
                ->visible(fn (Get $get): bool => $get('nationality') === 'Ethiopia')
                ->required(fn (Get $get): bool => $get('nationality') === 'Ethiopia'),
            TextInput::make('kebele')
                ->maxLength(100)
                ->visible(fn (Get $get): bool => $get('nationality') === 'Ethiopia'),
            Select::make('grade_id')
                ->relationship('grade', 'name')
                ->searchable()
                ->preload()
                ->rules(['exists:grades,id'])
                ->required(),
            Select::make('academic_year_id')
                ->relationship('academicYear', 'name')
                ->searchable()
                ->preload()
                ->rules(['exists:academic_years,id'])
                ->helperText('Changing the grade or academic year of an approved student clears an incompatible section assignment.')
                ->required(),
            TextInput::make('guardian_name')
                ->required()
                ->maxLength(255),
            TextInput::make('guardian_phone')
                ->required()
                ->maxLength(40),
            TextInput::make('guardian_email')
                ->email()
                ->maxLength(255)
                ->helperText('For approved applications, this updates an existing linked parent account. This form does not create new accounts.')
                ->nullable(),
            TextInput::make('guardian_relationship')
                ->maxLength(50),
            TextInput::make('guardian_occupation')
                ->maxLength(100),
            Textarea::make('guardian_address')
                ->maxLength(5000),
            TextInput::make('emergency_contact_name')
                ->maxLength(150),
            TextInput::make('emergency_contact_phone')
                ->maxLength(25),
            TextInput::make('previous_school')
                ->maxLength(255),
            TextInput::make('previous_grade')
                ->maxLength(100),
            Textarea::make('transfer_reason')
                ->maxLength(5000),
            Textarea::make('health_notes')
                ->maxLength(5000),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function countryOptions(): array
    {
        $codes = config('african_country_codes');
        $englishNames = iterator_to_array(ResourceBundle::create('en', 'ICUDATA-region')->get('Countries'));
        $localizedNames = iterator_to_array(ResourceBundle::create(app()->getLocale(), 'ICUDATA-region')->get('Countries'));
        $options = [];

        foreach ($codes as $code) {
            if (isset($englishNames[$code])) {
                $options[$englishNames[$code]] = $localizedNames[$code] ?? $englishNames[$code];
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function regionOptions(): array
    {
        $options = [];

        foreach (config('ethiopia_administrative_divisions') as $region) {
            $options[$region['name']] = $region['name'];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function zoneOptions(?string $regionName): array
    {
        $region = self::regionByName($regionName);
        $options = [];

        foreach ($region['zones'] ?? [] as $zone) {
            $options[$zone['name']] = $zone['name'];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function woredaOptions(?string $regionName, ?string $zoneName): array
    {
        $region = self::regionByName($regionName);
        $zone = null;

        foreach ($region['zones'] ?? [] as $candidate) {
            if ($candidate['name'] === $zoneName) {
                $zone = $candidate;
                break;
            }
        }

        $options = [];

        foreach ($zone['woredas'] ?? [] as $woreda) {
            $options[$woreda] = $woreda;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function regionByName(?string $name): ?array
    {
        foreach (config('ethiopia_administrative_divisions') as $region) {
            if ($region['name'] === $name) {
                return $region;
            }
        }

        return null;
    }
}
