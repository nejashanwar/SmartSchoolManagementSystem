<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'admission_application_id', 'admission_number',
        'grade_id', 'academic_year_id', 'section_id', 'full_name', 'status', 'admitted_at',
    ];

    protected function casts(): array
    {
        return ['admitted_at' => 'datetime'];
    }

    public static function newAdmissionNumber(AcademicYear $academicYear): string
    {
        $year = $academicYear->starts_on?->format('Y') ?? now()->format('Y');

        do {
            $number = 'MII-'.$year.'-'.Str::upper(Str::random(6));
        } while (static::query()->where('admission_number', $number)->exists());

        return $number;
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function application(): BelongsTo { return $this->belongsTo(AdmissionApplication::class, 'admission_application_id'); }
    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function examMarks(): HasMany { return $this->hasMany(ExamMark::class); }
    public function parentProfiles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany { return $this->belongsToMany(ParentProfile::class,'parent_student')->withTimestamps(); }
    public function results(): HasMany { return $this->hasMany(Result::class); }
}
