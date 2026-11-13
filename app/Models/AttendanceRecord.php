<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceRecord extends Model { protected $fillable=['attendance_session_id','student_id','section_id','recorded_by','attendance_date','status','note']; protected function casts(): array { return ['attendance_date'=>'date']; } public function session(): BelongsTo { return $this->belongsTo(AttendanceSession::class,'attendance_session_id'); } public function student(): BelongsTo { return $this->belongsTo(Student::class); } public function section(): BelongsTo { return $this->belongsTo(Section::class); } public function recorder(): BelongsTo { return $this->belongsTo(User::class,'recorded_by'); } }
