<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TeacherProfile;
use Illuminate\Support\Collection;

class TermMarksWorkflow
{
    public function canEnterMarks(Exam $exam): bool
    {
        if (! $exam->term_id) {
            return true;
        }

        $term = $exam->term ?? Term::query()->find($exam->term_id);

        return $term !== null && $this->isTermOpen($term, $exam->section ?? Section::query()->findOrFail($exam->section_id));
    }

    public function teacherOwns(TeacherProfile $teacher, ExamSubject $examSubject): bool
    {
        $query = $teacher->assignments()
            ->where('section_id', $examSubject->exam->section_id)
            ->where('subject_id', $examSubject->subject_id);

        if ($examSubject->exam->term_id) {
            $query->where(function ($assignments) use ($examSubject): void {
                $assignments->where('term_id', $examSubject->exam->term_id)->orWhereNull('term_id');
            });
        }

        return $query->exists();
    }

    public function isTermOpen(Term $term, Section $section): bool
    {
        if ((int) $term->academic_year_id !== (int) $section->academic_year_id) {
            return false;
        }

        $terms = Term::query()->where('academic_year_id', $term->academic_year_id)->orderBy('sort_order')->get();
        if ($terms->count() > 2 || ! $terms->contains('id', $term->id)) return false;

        $index = $terms->search(fn (Term $candidate): bool => (int) $candidate->id === (int) $term->id);
        if ($index === 0) return true;
        if ($terms->count() !== 2) return false;

        return $this->allRegistersSubmitted($section, $terms[$index - 1]);
    }

    public function allRegistersSubmitted(Section $section, Term $term): bool
    {
        if ((int) $term->academic_year_id !== (int) $section->academic_year_id) {
            return false;
        }

        $expectedSubjectIds = $this->assignmentSubjectIds($section, $term);
        $studentIds = $section->students()->pluck('id');

        if ($expectedSubjectIds->isEmpty() || $studentIds->isEmpty()) {
            return false;
        }

        $exams = Exam::query()
            ->where('section_id', $section->id)
            ->where('term_id', $term->id)
            ->with(['subjects.components'])
            ->get();

        if ($exams->isEmpty()) {
            return false;
        }

        $registers = $exams->flatMap->subjects;
        if ($registers->isEmpty()) {
            return false;
        }

        $coveredSubjectIds = $registers->pluck('subject_id')->unique();
        if ($expectedSubjectIds->diff($coveredSubjectIds)->isNotEmpty()) {
            return false;
        }

        foreach ($registers as $register) {
            if (! $expectedSubjectIds->contains($register->subject_id)
                || ! in_array($register->marks_status, ['submitted', 'approved'], true)
                || $register->components->isEmpty()) {
                return false;
            }

            $expectedMarks = $studentIds->count() * $register->components->count();
            $actualMarks = $register->marks()
                ->whereIn('student_id', $studentIds)
                ->whereIn('assessment_component_id', $register->components->modelKeys())
                ->whereIn('status', ['submitted', 'verified'])
                ->count();

            if ($actualMarks !== $expectedMarks) {
                return false;
            }
        }

        return true;
    }

    public function assignmentSubjectIds(Section $section, Term $term): Collection
    {
        return $section->teacherAssignments()
            ->where(function ($assignments) use ($term): void {
                $assignments->where('term_id', $term->id)->orWhereNull('term_id');
            })
            ->distinct()
            ->pluck('subject_id');
    }

    public function progress(Section $section, Term $term): array
    {
        $subjectIds = $this->assignmentSubjectIds($section, $term);
        $registers = ExamSubject::query()
            ->whereIn('exam_id', Exam::query()->select('id')->where('section_id', $section->id)->where('term_id', $term->id))
            ->with('subject')
            ->get();

        $pendingSubjects = $subjectIds->filter(function ($subjectId) use ($registers): bool {
            $subjectRegisters = $registers->where('subject_id', $subjectId);
            return $subjectRegisters->isEmpty()
                || $subjectRegisters->contains(fn (ExamSubject $register): bool => ! in_array($register->marks_status, ['submitted', 'approved'], true));
        })->map(fn ($subjectId) => Subject::query()->whereKey($subjectId)->value('name') ?? 'Subject #'.$subjectId)->values();

        return [
            'expected' => $subjectIds->count(),
            'submitted' => max(0, $subjectIds->count() - $pendingSubjects->count()),
            'pending_subjects' => $pendingSubjects,
            'complete' => $this->allRegistersSubmitted($section, $term),
        ];
    }
}
