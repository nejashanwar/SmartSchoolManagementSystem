<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'admission_opens_at',
        'admission_closes_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'admission_opens_at' => 'datetime',
            'admission_closes_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function admissionApplications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function terms(): HasMany { return $this->hasMany(Term::class); }
    public function sections(): HasMany { return $this->hasMany(Section::class); }

    public function isAdmissionOpen(): bool
    {
        $now = now();

        return $this->is_active
            && $this->admission_opens_at !== null
            && $this->admission_closes_at !== null
            && $now->greaterThanOrEqualTo($this->admission_opens_at)
            && $now->lessThanOrEqualTo($this->admission_closes_at);
    }
}
