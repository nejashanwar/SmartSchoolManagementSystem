<?php
namespace App\Http\Controllers;
use App\Models\AuditEvent;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentAttendanceReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class ParentPortalController extends Controller {
 public function login():View|RedirectResponse { if(Auth::check()&&Auth::user()->hasRole('PARENT'))return redirect()->route(Auth::user()->must_change_password?'parent.password.edit':'parent.dashboard');return view('parent.login'); }
 public function authenticate(Request $request):RedirectResponse { $data=$request->validate(['email'=>['required','email','max:255'],'password'=>['required','string','max:255']]);if(!Auth::attempt(['email'=>strtolower(trim($data['email'])),'password'=>$data['password'],'is_active'=>true]))return back()->withErrors(['email'=>'The login details were not recognized.'])->onlyInput('email');$user=Auth::user();if(!$user instanceof User||!$user->hasRole('PARENT')||!$user->parentProfile()->exists()){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return back()->withErrors(['email'=>'The login details were not recognized.'])->onlyInput('email');}$request->session()->regenerate();return redirect()->route($user->must_change_password?'parent.password.edit':'parent.dashboard'); }
 public function dashboard():View|RedirectResponse { $user=$this->parentUser();if($user->must_change_password)return redirect()->route('parent.password.edit');$parent=$user->parentProfile()->with(['students.grade','students.academicYear','students.section'])->firstOrFail();return view('parent.dashboard',compact('parent')); }
 public function child(Request $request,Student $student,StudentAttendanceReport $attendanceReport):View|RedirectResponse { $user=$this->parentUser();if($user->must_change_password)return redirect()->route('parent.password.edit');$parent=$user->parentProfile()->firstOrFail();abort_unless($parent->students()->whereKey($student->id)->exists(),404);$student->load(['grade','academicYear','section']);$report=$attendanceReport->for($student,$request->query('month'));$results=$student->results()->where('status','published')->with(['exam.examType','exam.term'])->orderByDesc('published_at')->get();return view('parent.child',compact('parent','student','report','results')); }
 public function reportCard(Student $student,Result $result):View|RedirectResponse { $user=$this->parentUser();if($user->must_change_password)return redirect()->route('parent.password.edit');$parent=$user->parentProfile()->firstOrFail();abort_unless($parent->students()->whereKey($student->id)->exists(),404);abort_unless($result->student_id===$student->id&&$result->status==='published',404);return $this->renderReportCard($result); }
 public function editPassword():View|RedirectResponse { $user=$this->parentUser();if(!$user->must_change_password)return redirect()->route('parent.dashboard');return view('parent.change-password'); }
 public function updatePassword(Request $request):RedirectResponse { $user=$this->parentUser();$data=$request->validate(['current_password'=>['required','current_password:web'],'password'=>['required','string','min:12','confirmed','different:current_password']]);$user->update(['password'=>$data['password'],'must_change_password'=>false]);AuditEvent::record('parent.password_changed',$user);$request->session()->regenerate();return redirect()->route('parent.dashboard')->with('status','Password updated.'); }
 public function logout(Request $request):RedirectResponse { Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('parent.login'); }
 private function parentUser():User { $user=Auth::user();abort_unless($user instanceof User&&$user->hasRole('PARENT')&&$user->parentProfile()->exists(),403);return $user; }
 private function renderReportCard(Result $result):View { $result->load(['student','exam.examType','exam.term','exam.section']);$result->exam->load(['subjects'=>fn($q)=>$q->with(['subject','components.marks'=>fn($m)=>$m->where('student_id',$result->student_id)->where('status','verified')])]);return view('student.report-card',compact('result')); }
}
