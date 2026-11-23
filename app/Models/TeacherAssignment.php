<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    protected $fillable = ['teacher_profile_id', 'section_id', 'term_id', 'subject_id'];

    public function teacherProfile(): BelongsTo { return $this->belongsTo(TeacherProfile::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function term(): BelongsTo { return $this->belongsTo(Term::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}
