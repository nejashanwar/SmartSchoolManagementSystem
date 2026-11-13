<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function teacherAssignments(): HasMany { return $this->hasMany(TeacherAssignment::class); }
    public function examSubjects(): HasMany { return $this->hasMany(ExamSubject::class); }
}
