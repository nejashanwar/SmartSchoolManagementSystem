<?php
namespace App\Filament\Resources\Students\Schemas;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
class StudentForm { public static function configure(Schema $schema): Schema { return $schema->components([
    Select::make('section_id')->label('Section')->placeholder('Not assigned')->options(fn ($record) => $record ? Section::query()->where('grade_id', $record->grade_id)->where('academic_year_id', $record->academic_year_id)->orderBy('name')->pluck('name', 'id') : [])->rules(fn ($record) => $record ? [Rule::exists('sections', 'id')->where('grade_id', $record->grade_id)->where('academic_year_id', $record->academic_year_id)] : [])->searchable()->preload()->nullable()
]); } }
