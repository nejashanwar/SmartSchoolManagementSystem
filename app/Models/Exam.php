<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Exam extends Model { protected $fillable=['academic_year_id','exam_type_id','term_id','section_id','name','exam_date','status']; protected function casts(): array { return ['exam_date'=>'date']; } public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); } public function examType(): BelongsTo { return $this->belongsTo(ExamType::class); } public function term(): BelongsTo { return $this->belongsTo(Term::class); } public function section(): BelongsTo { return $this->belongsTo(Section::class); } public function subjects(): HasMany { return $this->hasMany(ExamSubject::class); } public function results(): HasMany { return $this->hasMany(Result::class); } }
