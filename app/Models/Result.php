<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Result extends Model { protected $fillable=['exam_id','student_id','total_marks','maximum_marks','average_percentage','section_rank','cumulative_average_percentage','cumulative_rank','grade_label','status','published_by','published_at']; protected function casts(): array { return ['total_marks'=>'decimal:2','maximum_marks'=>'decimal:2','average_percentage'=>'decimal:2','section_rank'=>'integer','cumulative_average_percentage'=>'decimal:2','cumulative_rank'=>'integer','published_at'=>'datetime']; } public function exam(): BelongsTo { return $this->belongsTo(Exam::class); } public function student(): BelongsTo { return $this->belongsTo(Student::class); } public function publisher(): BelongsTo { return $this->belongsTo(User::class,'published_by'); } }
