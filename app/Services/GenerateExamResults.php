<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Exam;
use App\Models\GradeScale;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateExamResults
{
    public function generate(Exam $exam, User $actor): int
    {
        return $this->generateResults($exam, $actor, true, true);
    }

    public function generateFromSubmittedMarks(Exam $exam, User $actor): int
    {
        if (in_array($exam->status, ['completed', 'published'], true)) {
            return 0;
        }

        if ($exam->subjects()->whereNotIn('marks_status', ['submitted', 'approved'])->exists()) {
            return 0;
        }

        return $this->generateResults($exam, $actor, false, false);
    }

    private function generateResults(Exam $exam, User $actor, bool $requireApproval, bool $completeExam): int
    {
        if ($exam->status === 'published') {
            throw ValidationException::withMessages(['exam' => 'Published results cannot be regenerated.']);
        }

        return DB::transaction(function () use ($exam, $actor, $requireApproval, $completeExam): int {
            $exam->load(['section.students', 'subjects.subject', 'subjects.components.marks']);
            $students = $exam->section->students;

            if ($exam->subjects->isEmpty() || $students->isEmpty()) {
                throw ValidationException::withMessages(['exam' => 'Add at least one exam subject and one student in the section first.']);
            }

            $scales = GradeScale::query()->where('is_active', true)->orderByDesc('min_percentage')->get();
            if ($scales->isEmpty()) {
                throw ValidationException::withMessages(['exam' => 'Configure the school grade scale before generating results.']);
            }

            foreach ($students as $student) {
                $totalMarks = 0.0;
                $maximumMarks = 0.0;

                foreach ($exam->subjects as $examSubject) {
                    $allowedRegisterStatuses = $requireApproval ? ['approved'] : ['submitted', 'approved'];
                    if (! in_array($examSubject->marks_status, $allowedRegisterStatuses, true)) {
                        throw ValidationException::withMessages(['marks' => "The {$examSubject->subject->name} marks register is not ready for result calculation."]);
                    }

                    if ($examSubject->components->isEmpty()) {
                        throw ValidationException::withMessages(['marks' => "Configure assessment types for {$examSubject->subject->name} first."]);
                    }

                    $weightTotal = round((float) $examSubject->components->sum('weight'), 2);
                    if ($weightTotal !== 100.0) {
                        throw ValidationException::withMessages(['marks' => "The {$examSubject->subject->name} assessment weights must total 100%."]);
                    }

                    $subjectPercentage = 0.0;
                    foreach ($examSubject->components as $component) {
                        $mark = $component->marks->firstWhere('student_id', $student->id);
                        $allowedMarkStatuses = $requireApproval ? ['verified'] : ['submitted', 'verified'];
                        if (! $mark || ! in_array($mark->status, $allowedMarkStatuses, true)) {
                            throw ValidationException::withMessages(['marks' => "Every student must have a submitted {$component->name} mark for {$examSubject->subject->name}."]);
                        }

                        $maxMarks = (float) $component->max_marks;
                        if ($maxMarks <= 0 || (float) $mark->marks_obtained > $maxMarks) {
                            throw ValidationException::withMessages(['marks' => "A {$component->name} mark exceeds its maximum; correct it before generating results."]);
                        }

                        $subjectPercentage += ((float) $mark->marks_obtained / $maxMarks) * (float) $component->weight;
                    }

                    $totalMarks += round($subjectPercentage, 2);
                    $maximumMarks += 100;
                }

                $percentage = $maximumMarks > 0 ? round($totalMarks / $maximumMarks * 100, 2) : 0;
                $scale = $scales->first(fn ($band): bool => (float) $band->min_percentage <= $percentage && (float) $band->max_percentage >= $percentage);
                if (! $scale) {
                    throw ValidationException::withMessages(['grade_scale' => 'The configured grade scale does not cover '.$percentage.'%. Update the grade bands before generating results.']);
                }

                $result = Result::query()->updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $student->id],
                    [
                        'total_marks' => round($totalMarks, 2),
                        'maximum_marks' => round($maximumMarks, 2),
                        'average_percentage' => $percentage,
                        'grade_label' => $scale->label,
                        'status' => 'draft',
                        'published_by' => null,
                        'published_at' => null,
                    ],
                );

                AuditEvent::record('result.generated', $result, [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'average_percentage' => $percentage,
                    'grade_label' => $scale->label,
                    'generated_by' => $actor->id,
                ]);
            }

            $rank = 0;
            $position = 0;
            $previousAverage = null;
            $results = $exam->results()
                ->whereIn('student_id', $students->modelKeys())
                ->orderByDesc('average_percentage')
                ->orderBy('student_id')
                ->get();

            foreach ($results as $result) {
                $position++;
                $average = number_format((float) $result->average_percentage, 2, '.', '');
                if ($average !== $previousAverage) {
                    $rank = $position;
                    $previousAverage = $average;
                }
                $result->update(['section_rank' => $rank]);
            }

            if ($completeExam && $exam->status === 'draft') {
                $exam->update(['status' => 'completed']);
            }

            if ($exam->term_id) {
                $this->calculateCumulativeForSecondSemester($exam);
            }

            return $students->count();
        });
    }

    private function calculateCumulativeForSecondSemester(Exam $exam): void
    {
        $term = $exam->term;
        if (! $term) return;

        $terms = $term->academicYear->terms()->orderBy('sort_order')->get();
        if ($terms->count() !== 2 || (int) $terms->last()->id !== (int) $term->id) return;
        $firstTerm = $terms->first();

        $firstSemesterExams = Exam::query()
            ->where('section_id', $exam->section_id)
            ->where('term_id', $firstTerm->id)
            ->whereHas('results')
            ->get();
        if ($firstSemesterExams->count() !== 1) return;

        $firstResults = Result::query()->where('exam_id', $firstSemesterExams->first()->id)->get()->keyBy('student_id');
        $secondResults = Result::query()->where('exam_id', $exam->id)->get()->keyBy('student_id');
        $rankable = [];

        foreach ($secondResults as $studentId => $secondResult) {
            $firstResult = $firstResults->get($studentId);
            if (! $firstResult) {
                $secondResult->update(['cumulative_average_percentage' => null, 'cumulative_rank' => null]);
                continue;
            }

            $cumulativeAverage = round(((float) $firstResult->average_percentage + (float) $secondResult->average_percentage) / 2, 2);
            $secondResult->update(['cumulative_average_percentage' => $cumulativeAverage, 'cumulative_rank' => null]);
            $rankable[$studentId] = ['result' => $secondResult, 'average' => $cumulativeAverage];
        }

        uasort($rankable, fn (array $left, array $right): int => ($right['average'] <=> $left['average']) ?: ($left['result']->student_id <=> $right['result']->student_id));
        $rank = 0;
        $position = 0;
        $previousAverage = null;
        foreach ($rankable as $row) {
            $position++;
            $averageKey = number_format($row['average'], 2, '.', '');
            if ($averageKey !== $previousAverage) {
                $rank = $position;
                $previousAverage = $averageKey;
            }
            $row['result']->update(['cumulative_rank' => $rank]);
        }
    }

    public function publish(Exam $exam, User $actor): int
    {
        if ($exam->status === 'published') {
            throw ValidationException::withMessages(['exam' => 'This examination is already published.']);
        }

        $this->ensureEarlierSemesterIsPublished($exam);

        $count = $this->generate($exam, $actor);
        DB::transaction(function () use ($exam, $actor): void {
            $exam->results()->update(['status' => 'published', 'published_by' => $actor->id, 'published_at' => now()]);
            $exam->update(['status' => 'published']);
            AuditEvent::record('results.published', $exam, ['result_count' => $exam->results()->count()]);
        });

        return $count;
    }

    private function ensureEarlierSemesterIsPublished(Exam $exam): void
    {
        $term = $exam->term;
        if (! $term) return;

        $terms = $term->academicYear->terms()->orderBy('sort_order')->get();
        if ($terms->count() !== 2 || (int) $terms->last()->id !== (int) $term->id) return;
        $firstTerm = $terms->first();

        $firstSemesterExams = Exam::query()
            ->where('section_id', $exam->section_id)
            ->where('term_id', $firstTerm->id)
            ->get();

        if ($firstSemesterExams->count() !== 1 || $firstSemesterExams->first()->status !== 'published') {
            throw ValidationException::withMessages(['exam' => 'Publish the complete first-semester results before publishing the final annual report card.']);
        }

        $expectedStudents = $exam->section->students()->count();
        $publishedFirstResults = $firstSemesterExams->first()->results()->where('status', 'published')->count();
        if ($expectedStudents === 0 || $publishedFirstResults !== $expectedStudents) {
            throw ValidationException::withMessages(['exam' => 'First-semester results must be published for every student before the annual card can be completed.']);
        }
    }
}
