<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AuditEvent;
use App\Models\ExamAssessmentComponent;
use App\Models\ExamSubject;
use App\Models\Student;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherPortalController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasRole('TEACHER')) {
            return redirect()->route('teacher.dashboard');
        }

        return view('teacher.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! Auth::attempt([
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'is_active' => true,
        ])) {
            return back()->withErrors(['email' => 'The login details were not recognized.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user instanceof User || ! $user->hasRole('TEACHER')
            || ! $user->teacherProfile()->where('employment_status', 'active')->exists()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'The login details were not recognized.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('teacher.dashboard');
    }

    public function dashboard(): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->hasRole('TEACHER'), 403);

        $teacher = $user->teacherProfile()->where('employment_status', 'active')
            ->with(['assignments.section.grade', 'assignments.section.academicYear', 'assignments.section.students', 'assignments.section.exams.subjects.subject', 'assignments.section.exams.subjects.components.marks', 'assignments.subject'])
            ->firstOrFail();

        $attendanceSessions = AttendanceSession::query()->with(['section.grade', 'section.students', 'records.recorder'])
            ->where('teacher_profile_id', $teacher->id)
            ->whereIn('section_id', $teacher->assignments->pluck('section_id')->unique())
            ->whereBetween('session_date', [today()->startOfMonth(), today()])
            ->orderByDesc('session_date')->get();
        $attendanceRowCount = $attendanceSessions->sum(fn (AttendanceSession $session): int => $session->section->students->count());

        return view('teacher.dashboard', compact('teacher', 'attendanceSessions', 'attendanceRowCount'));
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasRole('TEACHER'), 403);

        $data = $request->validate([
            'attendance_session_id' => ['required', 'integer', 'exists:attendance_sessions,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = $user->teacherProfile()->where('employment_status', 'active')->firstOrFail();
        $session = AttendanceSession::query()->findOrFail($data['attendance_session_id']);
        abort_unless($session->isEditableByTeacher(), 403, 'This attendance session has ended and can no longer be edited by a teacher.');
        abort_unless((int) $session->teacher_profile_id === (int) $profile->id, 403, 'This attendance session belongs to another teacher.');
        abort_unless($profile->assignments()->where('section_id', $session->section_id)->exists(), 403);
        $student = Student::query()->whereKey($data['student_id'])->where('section_id', $session->section_id)->firstOrFail();
        $record = AttendanceRecord::query()->updateOrCreate(
            ['attendance_session_id' => $session->id, 'student_id' => $student->id],
            ['section_id' => $session->section_id, 'attendance_date' => $session->session_date, 'recorded_by' => $user->id, 'status' => $data['status'], 'note' => $data['note'] ?? null],
        );

        AuditEvent::record('attendance.recorded', $record, ['attendance_session_id' => $session->id, 'student_id' => $student->id, 'section_id' => $session->section_id, 'date' => $session->session_date->toDateString(), 'status' => $data['status']]);

        $rowId = 'attendance-'.$session->id.'-'.$student->id.'-row';

        return back()
            ->with('attendance_saved_'.$session->id.'_'.$student->id, 'Attendance saved successfully.')
            ->withFragment($rowId);
    }

    public function storeMark(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasRole('TEACHER'), 403);

        $data = $request->validate([
            'exam_subject_id' => ['required', 'integer', 'exists:exam_subjects,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'marks' => ['required', 'array', 'min:1'],
            'marks.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

        $profile = $user->teacherProfile()->where('employment_status', 'active')->firstOrFail();
        $examSubject = ExamSubject::query()->with(['exam', 'components'])->findOrFail($data['exam_subject_id']);
        $this->authorizeExamSubject($profile, $examSubject);
        abort_unless(in_array($examSubject->marks_status, ['draft', 'returned'], true), 403, 'This marks register is locked while it is under review.');
        $student = Student::query()->whereKey($data['student_id'])->where('section_id', $examSubject->exam->section_id)->firstOrFail();

        $saved = [];
        foreach ($data['marks'] as $componentId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            /** @var ExamAssessmentComponent|null $component */
            $component = $examSubject->components->firstWhere('id', (int) $componentId);
            abort_unless($component, 422, 'An assessment component does not belong to this subject.');
            abort_if((float) $value > (float) $component->max_marks, 422, "Marks for {$component->name} cannot exceed {$component->max_marks}.");

            $mark = ExamAssessmentComponent::query()->findOrFail($component->id)->marks()->updateOrCreate(
                ['student_id' => $student->id],
                ['exam_subject_id' => $examSubject->id, 'entered_by' => $user->id, 'marks_obtained' => $value, 'status' => 'draft'],
            );
            $saved[$component->id] = $mark->marks_obtained;
        }

        if ($saved === []) {
            throw ValidationException::withMessages(['marks' => 'Enter at least one mark before saving this student row.']);
        }

        AuditEvent::record('exam.marks_saved', $examSubject, ['student_id' => $student->id, 'marks' => $saved]);

        $rowId = 'marks-'.$examSubject->id.'-'.$student->id.'-row';

        return back()
            ->with('marks_saved_'.$examSubject->id.'_'.$student->id, 'Student marks saved successfully as a draft.')
            ->withFragment($rowId);
    }

    public function saveAssessmentComponents(Request $request, ExamSubject $examSubject): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasRole('TEACHER'), 403);
        $profile = $user->teacherProfile()->where('employment_status', 'active')->firstOrFail();
        $examSubject->load('exam');
        $this->authorizeExamSubject($profile, $examSubject);
        abort_unless(in_array($examSubject->marks_status, ['draft', 'returned'], true), 403, 'This marks register is under review and its assessment setup is locked.');
        $examSubject->load('components.marks');

        $data = $request->validate([
            'components' => ['required', 'array', 'min:1', 'max:20'],
            'components.*.id' => ['nullable', 'integer'],
            'components.*.name' => ['required', 'string', 'max:80', 'distinct:ignore_case'],
            'components.*.max_marks' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:999999.99'],
            'components.*.weight' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:100'],
        ]);
        $componentIds = collect($data['components'])->pluck('id')->filter()->values();
        if ($componentIds->count() !== $componentIds->unique()->count()) {
            throw ValidationException::withMessages(['components' => 'An existing assessment type cannot be listed more than once.']);
        }
        $weightTotal = round(collect($data['components'])->sum(fn (array $component): float => (float) $component['weight']), 2);
        if ($weightTotal !== 100.0) {
            throw ValidationException::withMessages(['components' => "Assessment weights must total exactly 100%. Current total: {$weightTotal}%."]);
        }

        DB::transaction(function () use ($examSubject, $data, $user): void {
            $existing = $examSubject->components()->withCount('marks')->get()->keyBy('id');
            $retainedIds = collect($data['components'])->pluck('id')->filter()->map(fn ($id): int => (int) $id)->all();

            foreach ($existing as $componentId => $component) {
                if (in_array((int) $componentId, $retainedIds, true)) {
                    continue;
                }
                abort_if($component->marks_count > 0, 422, "{$component->name} already has marks and cannot be removed.");
                $component->delete();
            }

            foreach (array_values($data['components']) as $index => $input) {
                $values = [
                    'name' => trim($input['name']),
                    'max_marks' => $input['max_marks'],
                    'weight' => $input['weight'],
                    'sort_order' => $index + 1,
                ];

                if (filled($input['id'] ?? null)) {
                    $component = $existing->get((int) $input['id']);
                    abort_unless($component, 422, 'An assessment type does not belong to this subject.');
                    abort_if($component->marks()->where('marks_obtained', '>', $input['max_marks'])->exists(), 422, "The new maximum for {$component->name} is below a mark already entered.");
                    $component->update($values);
                } else {
                    $examSubject->components()->create($values);
                }
            }
            $examSubject->update(['marks_status' => 'draft', 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'review_note' => null]);
            AuditEvent::record('exam.assessment_setup_saved', $examSubject, ['components' => $data['components'], 'saved_by' => $user->id]);
        });

        return back()->with('status', 'Assessment types and weights saved. Weights total 100%.');
    }

    public function submitAssessmentMarks(Request $request, ExamSubject $examSubject): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasRole('TEACHER'), 403);
        $profile = $user->teacherProfile()->where('employment_status', 'active')->firstOrFail();
        $examSubject->load(['exam.section.students', 'components']);
        $this->authorizeExamSubject($profile, $examSubject);
        abort_unless(in_array($examSubject->marks_status, ['draft', 'returned'], true), 403, 'This marks register has already been submitted or approved.');

        $componentIds = $examSubject->components->modelKeys();
        $studentIds = $examSubject->exam->section->students->modelKeys();
        $registerErrorKey = 'marks_register_'.$examSubject->id;

        if ($componentIds === [] || $studentIds === []) {
            throw ValidationException::withMessages([$registerErrorKey => 'Add assessment types and students before submitting marks.']);
        }

        $marks = $examSubject->marks()->whereIn('assessment_component_id', $componentIds)->whereIn('student_id', $studentIds)->get();
        $expectedMarks = count($componentIds) * count($studentIds);
        if ($marks->count() !== $expectedMarks) {
            throw ValidationException::withMessages([$registerErrorKey => 'Enter marks for every student in every assessment type before submitting.']);
        }

        foreach ($marks as $mark) {
            $component = $examSubject->components->firstWhere('id', $mark->assessment_component_id);

            if (! $component) {
                throw ValidationException::withMessages([$registerErrorKey => 'A saved mark refers to an assessment type outside this subject.']);
            }

            if ((float) $mark->marks_obtained > (float) $component->max_marks) {
                throw ValidationException::withMessages([$registerErrorKey => "A saved {$component->name} mark exceeds its maximum."]);
            }
        }

        DB::transaction(function () use ($examSubject, $user): void {
            $examSubject->marks()->update(['status' => 'submitted']);
            $examSubject->update(['marks_status' => 'submitted', 'submitted_at' => now(), 'review_note' => null]);
            AuditEvent::record('exam.marks_submitted', $examSubject, ['submitted_by' => $user->id, 'mark_count' => $examSubject->marks()->count()]);
        });

        return back()->with('status', 'Marks register submitted to Admin for review.');
    }

    private function authorizeExamSubject(TeacherProfile $profile, ExamSubject $examSubject): void
    {
        abort_unless(! in_array($examSubject->exam->status, ['completed', 'published'], true), 403, 'Completed or published examination marks are locked.');
        abort_unless($profile->assignments()->where('section_id', $examSubject->exam->section_id)->where('subject_id', $examSubject->subject_id)->exists(), 403);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.login');
    }
}
