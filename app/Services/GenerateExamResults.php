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
        if ($exam->status === 'published') {
            throw ValidationException::withMessages(['exam' => 'Published results cannot be regenerated.']);
        }

        return DB::transaction(function () use ($exam, $actor): int {
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
                    if ($examSubject->marks_status !== 'approved') {
                        throw ValidationException::withMessages(['marks' => "The {$examSubject->subject->name} marks register must be approved by Admin before generating results."]);
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
                        if (! $mark || $mark->status !== 'verified') {
                            throw ValidationException::withMessages(['marks' => "Every student must have an Admin-approved {$component->name} mark for {$examSubject->subject->name}."]);
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

            if ($exam->status === 'draft') {
                $exam->update(['status' => 'completed']);
            }

            return $students->count();
        });
    }

    public function publish(Exam $exam, User $actor): int
    {
        if ($exam->status === 'published') {
            throw ValidationException::withMessages(['exam' => 'This examination is already published.']);
        }

        $count = $this->generate($exam, $actor);
        DB::transaction(function () use ($exam, $actor): void {
            $exam->results()->update(['status' => 'published', 'published_by' => $actor->id, 'published_at' => now()]);
            $exam->update(['status' => 'published']);
            AuditEvent::record('results.published', $exam, ['result_count' => $exam->results()->count()]);
        });

        return $count;
    }
}
