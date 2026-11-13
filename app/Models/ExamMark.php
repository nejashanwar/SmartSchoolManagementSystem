<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ExamMark extends Model { protected $fillable=['exam_subject_id','assessment_component_id','student_id','entered_by','marks_obtained','status']; protected function casts(): array { return ['marks_obtained'=>'decimal:2']; } public function examSubject(): BelongsTo { return $this->belongsTo(ExamSubject::class); } public function assessmentComponent(): BelongsTo { return $this->belongsTo(ExamAssessmentComponent::class,'assessment_component_id'); } public function student(): BelongsTo { return $this->belongsTo(Student::class); } public function examiner(): BelongsTo { return $this->belongsTo(User::class,'entered_by'); } }
