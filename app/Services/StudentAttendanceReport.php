<?php
namespace App\Services;
use App\Models\AttendanceSession;
use App\Models\Student;
use Carbon\CarbonImmutable;
class StudentAttendanceReport {
 public function for(Student $student, ?string $requestedMonth = null): array {
  $month=is_string($requestedMonth)&&preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$requestedMonth)?$requestedMonth:now()->format('Y-m');
  $start=CarbonImmutable::createFromFormat('!Y-m',$month)->startOfMonth();$end=$start->endOfMonth();
  $sessions=$student->section_id?AttendanceSession::query()->with(['teacherProfile.user','records'=>fn($q)=>$q->where('student_id',$student->id)])
   ->where('section_id',$student->section_id)->where('status','completed')->whereBetween('session_date',[$start->toDateString(),$end->toDateString()])->orderBy('session_date')->orderBy('teacher_profile_id')->orderBy('name')->get():collect();
  $statuses=$sessions->map(fn($session)=>$session->records->first()?->status??'not_recorded')->countBy();
  $present=(int)($statuses['present']??0);$late=(int)($statuses['late']??0);$count=$sessions->count();
  return ['month'=>$month,'sessions'=>$sessions,'session_count'=>$count,'statuses'=>$statuses,'attendance_percentage'=>$count?round(($present+$late)/$count*100,1):null];
 }
}
