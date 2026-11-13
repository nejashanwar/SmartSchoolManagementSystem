<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AdmissionApplication extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CORRECTION_REQUESTED = 'correction_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reference_number', 'student_name', 'guardian_name', 'guardian_phone',
        'guardian_email', 'previous_school', 'grade_id', 'academic_year_id',
        'status', 'review_reason', 'reviewed_by', 'reviewed_at', 'submitted_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public static function newReferenceNumber(): string
    {
        do {
            $reference = 'MII-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (static::query()->where('reference_number', $reference)->exists());

        return $reference;
    }

    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function student(): HasOne { return $this->hasOne(Student::class); }
}
