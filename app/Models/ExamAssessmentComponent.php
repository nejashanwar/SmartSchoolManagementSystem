<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAssessmentComponent extends Model
{
    protected $fillable = ['exam_subject_id', 'name', 'max_marks', 'weight', 'sort_order'];

    protected function casts(): array
    {
        return ['max_marks' => 'decimal:2', 'weight' => 'decimal:2'];
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'assessment_component_id');
    }
}
