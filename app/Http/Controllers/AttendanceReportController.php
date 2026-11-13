<?php
namespace App\Http\Controllers;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Section;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\AttendancePdfExporter;
use App\Services\AttendanceXlsxExporter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AttendanceReportController extends Controller {
 public function show(Request $request) {
  $report=$this->reportData($request);
  return view('attendance.reports',$report);
 }

 public function download(Request $request,string $format) {
  abort_unless(in_array($format,['csv','xlsx','pdf'],true),404);
  $report=$this->reportData($request);$sessions=$report['reports'];$from=$report['from'];$until=$report['until'];
  if($format==='xlsx')return app(AttendanceXlsxExporter::class)->download($sessions,$from,$until);
  if($format==='pdf')return app(AttendancePdfExporter::class)->download($sessions,$from,$until);
  $filename='attendance-report-'.$from.'-to-'.$until.'.csv';
  return response()->streamDownload(function()use($sessions){$out=fopen('php://output','w');fputcsv($out,['Date','Session','Session status','Attendance teacher','Grade','Section','Student','Admission number','Attendance status','Recorded by','Marked','Students','Present','Absent','Late','Excused','Not recorded','Completion percentage']);foreach($sessions as $report){$session=$report['session'];foreach($report['rows'] as $row)fputcsv($out,[$session->session_date->toDateString(),$session->name,$session->status,$session->teacherProfile?->user?->name??'',$session->section->grade->name,$session->section->name,$row['student']->full_name,$row['student']->admission_number,$row['status'],$row['recorded_by']??'',$report['marked'],$report['total'],$report['counts']['present'],$report['counts']['absent'],$report['counts']['late'],$report['counts']['excused'],$report['counts']['not_recorded'],$report['completion']]);}fclose($out);},$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
 }

 public function downloadCsv(Request $request) { return $this->download($request,'csv'); }

 private function reportData(Request $request):array {
  $user=$request->user();abort_unless($user instanceof User,403);
  $canAdminister=$user->can('ViewAny:AttendanceRecord');$teacherProfile=null;
  if(!$canAdminister){abort_unless($user->hasRole('TEACHER'),403);$teacherProfile=$user->teacherProfile()->where('employment_status','active')->firstOrFail();}
  $input=$request->validate(['from'=>['nullable','date_format:Y-m-d'],'until'=>['nullable','date_format:Y-m-d'],'section_id'=>['nullable','integer','exists:sections,id']]);
  $from=$input['from']??today()->toDateString();$until=$input['until']??today()->toDateString();
  $start=CarbonImmutable::createFromFormat('!Y-m-d',$from);$end=CarbonImmutable::createFromFormat('!Y-m-d',$until);
  if($end->lessThan($start))throw ValidationException::withMessages(['until'=>'The end date must be the same as or later than the start date.']);
  if($start->diffInDays($end)>366)throw ValidationException::withMessages(['from'=>'Attendance reports are limited to one year at a time.']);
  $sectionIds=$teacherProfile?->assignments()->pluck('section_id')->unique()->values();
  if($teacherProfile&&filled($input['section_id']??null)&&!$sectionIds->contains((int)$input['section_id']))abort(403);
  $query=AttendanceSession::query()->with(['section.grade','section.students','teacherProfile.user','records.recorder'])
   ->whereBetween('session_date',[$from,$until]);
  if($teacherProfile)$query->where('teacher_profile_id',$teacherProfile->id)->whereIn('section_id',$sectionIds);
  if(filled($input['section_id']??null))$query->where('section_id',$input['section_id']);
  $reports=$query->orderBy('session_date')->orderBy('section_id')->orderBy('name')->get()
   ->map(fn(AttendanceSession $session)=>['session'=>$session,...$session->attendanceReportSummary()]);
  $sections=$teacherProfile
   ? Section::query()->with('grade')->whereIn('id',$sectionIds)->orderBy('grade_id')->get()
   : Section::query()->with('grade')->orderBy('grade_id')->get();
  return ['reports'=>$reports,'sections'=>$sections,'from'=>$from,'until'=>$until,'selectedSection'=>$input['section_id']??'','isTeacher'=>(bool)$teacherProfile];
 }
}
