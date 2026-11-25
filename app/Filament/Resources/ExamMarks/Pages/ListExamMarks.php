<?php

namespace App\Filament\Resources\ExamMarks\Pages;

use App\Filament\Resources\ExamMarks\ExamMarkResource;
use App\Models\AuditEvent;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\GenerateExamResults;
use App\Services\TermMarksWorkflow;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ListExamMarks extends Page
{
    protected static string $resource = ExamMarkResource::class;

    protected string $view = 'filament.resources.exam-marks.pages.list-exam-marks';

    protected static ?string $title = 'Exam Marks';

    public ?int $examId = null;

    public string $search = '';

    public string $studentFilter = 'all';

    public function verifyStudentMarks(int $studentId): void
    {
        abort_unless(auth()->user()?->can('Update:ExamSubject'), 403);

        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        $exam = Exam::query()->with(['subjects.components', 'section.students'])->findOrFail($this->examId);
        abort_unless($exam->section->students->contains('id', $studentId), 404);
        abort_if(in_array($exam->status, ['completed', 'published'], true), 403);

        $studentMarksCount = 0;

        try {
            DB::transaction(function () use ($exam, $studentId, $actor, &$studentMarksCount): void {
                foreach ($exam->subjects as $examSubject) {
                    if (! in_array($examSubject->marks_status, ['submitted', 'approved'], true)) {
                        throw ValidationException::withMessages(['marks' => 'All subject registers must be submitted before student marks can be verified.']);
                    }

                    $expectedForStudent = $examSubject->components->count();
                    $marks = $examSubject->marks()->where('student_id', $studentId)->lockForUpdate()->get();

                    if ($expectedForStudent === 0 || $marks->count() !== $expectedForStudent) {
                        throw ValidationException::withMessages(['marks' => 'This student has incomplete marks in one or more subjects.']);
                    }

                    if ($marks->contains(fn ($mark) => ! in_array($mark->status, ['submitted', 'verified'], true))) {
                        throw ValidationException::withMessages(['marks' => 'Only submitted marks can be verified.']);
                    }

                    $examSubject->marks()->where('student_id', $studentId)->update(['status' => 'verified']);
                    $studentMarksCount += $marks->count();

                    $expectedForSubject = $exam->section->students->count() * $expectedForStudent;
                    $verifiedForSubject = $examSubject->marks()->where('status', 'verified')->count();

                    if ($verifiedForSubject === $expectedForSubject && $examSubject->marks_status !== 'approved') {
                        $examSubject->update([
                            'marks_status' => 'approved',
                            'reviewed_by' => $actor->id,
                            'reviewed_at' => now(),
                            'review_note' => null,
                        ]);
                        AuditEvent::record('exam.marks_approved', $examSubject, ['approved_by' => $actor->id, 'mark_count' => $verifiedForSubject]);
                    }
                }
            });
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title('Marks were not verified')->body(collect($exception->errors())->flatten()->implode(' '))->send();

            return;
        }

        AuditEvent::record('exam.student_marks_verified', $exam, ['student_id' => $studentId, 'verified_by' => $actor->id, 'mark_count' => $studentMarksCount]);

        $allRegistersApproved = ! $exam->subjects()->where('marks_status', '!=', 'approved')->exists();

        if (! $allRegistersApproved) {
            Notification::make()->success()->title('Student marks verified')->body('Other students’ marks still need review before results can be calculated.')->send();

            return;
        }

        try {
            $count = app(GenerateExamResults::class)->generate($exam, $actor);
            Notification::make()->success()->title('Student marks verified; results calculated')->body("Calculated results and section ranks for {$count} students. Review and publish them from the examination page.")->send();
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title('Marks verified; results need attention')->body(collect($exception->errors())->flatten()->implode(' '))->send();
        }
    }

    public function mount(): void
    {
        $this->examId = Exam::query()->whereHas('subjects')->latest('id')->value('id');
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->url(ExamMarkResource::getUrl('create'))];
    }

    protected function getViewData(): array
    {
        $termWorkflow = app(TermMarksWorkflow::class);
        $exams = Exam::query()
            ->with(['section.grade', 'subjects.subject', 'term.academicYear.terms'])
            ->whereHas('subjects')
            ->latest('id')
            ->get();

        $exam = $exams->firstWhere('id', $this->examId);

        if (! $exam) {
            return ['exams' => $exams, 'exam' => null, 'components' => collect(), 'students' => collect(), 'marks' => collect(), 'subjectAverages' => [], 'results' => collect(), 'semesterProgress' => null, 'semesterGateOpen' => false, 'progressTerm' => null];
        }

        $exam->load(['subjects.components', 'section.teacherAssignments.subject', 'term.academicYear.terms']);
        $terms = $exam->term?->academicYear?->terms?->sortBy('sort_order')->values() ?? collect();
        $termIndex = $terms->search(fn ($term): bool => (int) $term->id === (int) $exam->term_id);
        $progressTerm = $termIndex !== false && $termIndex > 0 ? $terms[$termIndex - 1] : $exam->term;
        $semesterProgress = $progressTerm ? $termWorkflow->progress($exam->section, $progressTerm) : null;
        $semesterGateOpen = $exam->term ? $termWorkflow->isTermOpen($exam->term, $exam->section) : true;
        $components = $exam->subjects
            ->flatMap(fn ($examSubject) => $examSubject->components->map(function ($component) use ($examSubject) {
                $component->setRelation('examSubject', $examSubject);

                return $component;
            }))
            ->values();

        $students = Student::query()
            ->where('section_id', $exam->section_id)
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'admission_number']);

        $marks = ExamMark::query()
            ->whereIn('assessment_component_id', $components->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get(['student_id', 'assessment_component_id', 'marks_obtained', 'status'])
            ->keyBy(fn ($mark) => $mark->student_id.'-'.$mark->assessment_component_id);

        if ($this->studentFilter !== 'all') {
            $students = $students->filter(function ($student) use ($components, $marks): bool {
                $studentMarks = $components->map(fn ($component) => $marks->get($student->id.'-'.$component->id));
                $hasSubmittedMarks = $studentMarks->isNotEmpty()
                    && $studentMarks->every(fn ($mark): bool => $mark && in_array($mark->status, ['submitted', 'verified'], true));
                $allMarksVerified = $hasSubmittedMarks
                    && $studentMarks->every(fn ($mark): bool => $mark->status === 'verified');

                return match ($this->studentFilter) {
                    'verified' => $allMarksVerified,
                    'ready' => $hasSubmittedMarks && ! $allMarksVerified,
                    'incomplete' => ! $hasSubmittedMarks,
                    default => true,
                };
            })->values();
        }

        $subjectAverages = [];
        foreach ($students as $student) {
            foreach ($exam->subjects as $examSubject) {
                $weightedAverage = 0.0;
                $complete = $examSubject->components->isNotEmpty();

                foreach ($examSubject->components as $component) {
                    $mark = $marks->get($student->id.'-'.$component->id);
                    $maximum = (float) $component->max_marks;

                    if (! $mark || $maximum <= 0) {
                        $complete = false;
                        break;
                    }

                    $weightedAverage += ((float) $mark->marks_obtained / $maximum) * (float) $component->weight;
                }

                $subjectAverages[$student->id][$examSubject->id] = $complete ? round($weightedAverage, 2) : null;
            }
        }

        $results = Result::query()
            ->where('exam_id', $exam->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return compact('exams', 'exam', 'components', 'students', 'marks', 'subjectAverages', 'results', 'semesterProgress', 'semesterGateOpen', 'progressTerm');
    }
}
