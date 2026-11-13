<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['grade_id', 'academic_year_id', 'name', 'capacity', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function teacherAssignments(): HasMany { return $this->hasMany(TeacherAssignment::class); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function attendanceSessions(): HasMany { return $this->hasMany(AttendanceSession::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
}
