<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonImmutable;
class AttendanceSession extends Model {
 protected $fillable=['section_id','teacher_profile_id','created_by','session_date','name','starts_at','ends_at','status'];
 protected function casts():array{return ['session_date'=>'date','starts_at'=>'immutable_datetime','ends_at'=>'immutable_datetime'];}
 public function section():BelongsTo{return $this->belongsTo(Section::class);}
 public function teacherProfile():BelongsTo{return $this->belongsTo(TeacherProfile::class);}
 public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
 public function records():HasMany{return $this->hasMany(AttendanceRecord::class);}

 public function isEditableByTeacher(?CarbonImmutable $now=null):bool {
  if($this->status!=='open'||$this->session_date->isAfter(today()))return false;
  $now??=CarbonImmutable::now(config('app.timezone'));
  if(!$this->starts_at&&!$this->ends_at&&!$this->session_date->isSameDay($now))return false;
  if($this->starts_at&&$now->lt($this->starts_at))return false;
  if($this->ends_at&&$now->gte($this->ends_at))return false;
  return true;
 }

 public function attendanceReportSummary():array {
  $students=$this->section->students;
  $records=$this->records->keyBy('student_id');
  $counts=['present'=>0,'absent'=>0,'late'=>0,'excused'=>0,'not_recorded'=>0];
  $rows=$students->sortBy('full_name')->map(function(Student $student)use($records,&$counts):array{
   $record=$records->get($student->id);$status=$record?->status??'not_recorded';$counts[$status]=($counts[$status]??0)+1;
   return ['student'=>$student,'status'=>$status,'recorded_by'=>$record?->recorder?->name];
  })->values();
  $total=$students->count();$marked=$total-$counts['not_recorded'];
  return ['rows'=>$rows,'counts'=>$counts,'total'=>$total,'marked'=>$marked,'unmarked'=>$counts['not_recorded'],'completion'=>$total?round($marked/$total*100,1):0];
 }
}
