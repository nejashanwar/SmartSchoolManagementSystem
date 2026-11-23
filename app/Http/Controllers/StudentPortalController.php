<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Exam;
use App\Models\Result;
use App\Services\StudentAttendanceReport;
use App\Services\BuildStudentReportCard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasRole('STUDENT')) {
            return redirect()->route(Auth::user()->must_change_password ? 'student.password.edit' : 'student.dashboard');
        }

        return view('student.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials = [
            'username' => strtoupper(trim($data['username'])),
            'password' => $data['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'The login details were not recognized.'])->onlyInput('username');
        }

        $user = Auth::user();

        if (! $user instanceof User || ! $user->hasRole('STUDENT') || ! $user->student()->exists()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => 'The login details were not recognized.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->route($user->must_change_password ? 'student.password.edit' : 'student.dashboard');
    }

    public function dashboard(StudentAttendanceReport $attendanceReport): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->hasRole('STUDENT') && $user->student()->exists(), 403);

        if ($user->must_change_password) {
            return redirect()->route('student.password.edit');
        }

        $student = $user->student()->with(['grade', 'academicYear', 'section', 'application'])->firstOrFail();
        $subjectAssignments = $student->section
            ? $student->section->teacherAssignments()
                ->with(['subject', 'teacherProfile.user'])
                ->orderBy('subject_id')
                ->get()
            : collect();
        $attendanceSummary = $student->attendanceRecords()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $exams = $student->section_id
            ? Exam::query()->with(['term', 'subjects.subject'])->where('section_id', $student->section_id)
                ->where('status', '!=', 'draft')->orderBy('exam_date')->get()
            : collect();
        $results = $student->results()->where('status', 'published')->with(['exam.examType', 'exam.term'])->orderByDesc('published_at')->get();
        $monthlyAttendance = $attendanceReport->for($student);

        return view('student.dashboard', compact('student', 'subjectAssignments', 'attendanceSummary', 'exams', 'results', 'monthlyAttendance'));
    }

    public function attendance(Request $request, StudentAttendanceReport $attendanceReport): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->hasRole('STUDENT'), 403);
        if ($user->must_change_password) return redirect()->route('student.password.edit');
        $student = $user->student()->firstOrFail();
        $report = $attendanceReport->for($student, $request->query('month'));
        return view('student.attendance-report', compact('student', 'report'));
    }

    public function reportCard(Result $result, BuildStudentReportCard $reportCard): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->hasRole('STUDENT'), 403);
        if ($user->must_change_password) return redirect()->route('student.password.edit');
        $student = $user->student()->firstOrFail();
        abort_unless($result->student_id === $student->id && $result->status === 'published', 404);
        $card = $reportCard->build($result);
        return view('student.report-card', compact('result', 'card'));
    }

    public function editPassword(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->hasRole('STUDENT') && $user->student()->exists(), 403);

        if (! $user->must_change_password) {
            return redirect()->route('student.dashboard');
        }

        return view('student.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->hasRole('STUDENT') && $user->student()->exists(), 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:12', 'confirmed', 'different:current_password'],
        ]);

        $user->update([
            'password' => $data['password'],
            'must_change_password' => false,
        ]);

        AuditEvent::record('student.password_changed', $user, [
            'student_id' => $user->student()->value('id'),
        ]);

        $request->session()->regenerate();

        return redirect()->route('student.dashboard')->with('status', 'Password updated.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
