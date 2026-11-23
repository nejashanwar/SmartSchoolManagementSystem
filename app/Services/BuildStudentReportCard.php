<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Result;

class BuildStudentReportCard
{
    public function build(Result $result): array
    {
        $result->load(['student.grade', 'exam.examType', 'exam.term.academicYear.terms', 'exam.section', 'exam.academicYear']);
        $exam = $result->exam;
        $terms = $exam->term?->academicYear?->terms?->sortBy('sort_order')->values() ?? collect();
        $firstTerm = $terms->first();
        $lastTerm = $terms->last();
        $firstSemesterResult = null;
        $secondSemesterResult = null;
        $firstSemesterLabel = $firstTerm?->name ?? 'Semester 1';
        $secondSemesterLabel = $lastTerm?->name ?? 'Semester 2';

        if ($terms->count() === 2 && $firstTerm && $lastTerm && $firstTerm->id !== $lastTerm->id) {
            if ((int) $exam->term_id === (int) $lastTerm->id) {
                $firstExam = Exam::query()
                    ->where('section_id', $exam->section_id)
                    ->where('term_id', $firstTerm->id)
                    ->where('status', 'published')
                    ->first();
                if ($firstExam) {
                    $firstSemesterResult = Result::query()
                        ->where('exam_id', $firstExam->id)
                        ->where('student_id', $result->student_id)
                        ->where('status', 'published')
                        ->with(['exam.subjects.subject', 'exam.subjects.components.marks' => fn ($query) => $query->where('student_id', $result->student_id)->where('status', 'verified')])
                        ->first();
                }
                $secondSemesterResult = $result;
            } else {
                $firstSemesterResult = $result;
            }
        } else {
            $firstSemesterResult = $result;
            $firstSemesterLabel = $exam->term?->name ?? $exam->examType?->name ?? 'Examination';
            $secondSemesterLabel = '—';
        }

        $firstScores = $this->subjectScores($firstSemesterResult);
        $secondScores = $this->subjectScores($secondSemesterResult);
        $subjects = collect($firstScores)->keys()->merge(array_keys($secondScores))->unique()->map(function (int|string $subjectId) use ($firstScores, $secondScores, $firstSemesterResult, $secondSemesterResult): array {
            $subject = $firstSemesterResult?->exam?->subjects?->firstWhere('subject_id', $subjectId)?->subject
                ?? $secondSemesterResult?->exam?->subjects?->firstWhere('subject_id', $subjectId)?->subject;
            $first = $firstScores[$subjectId] ?? null;
            $second = $secondScores[$subjectId] ?? null;

            return [
                'name' => $subject?->name ?? 'Subject',
                'semester1' => $first,
                'semester2' => $second,
                'cumulative' => $first !== null && $second !== null ? round(($first + $second) / 2, 2) : null,
            ];
        })->values();

        return [
            'student' => $result->student,
            'exam' => $exam,
            'firstSemesterResult' => $firstSemesterResult,
            'secondSemesterResult' => $secondSemesterResult,
            'firstSemesterLabel' => $firstSemesterLabel,
            'secondSemesterLabel' => $secondSemesterLabel,
            'subjects' => $subjects,
            'cumulativeAverage' => $secondSemesterResult?->cumulative_average_percentage,
            'cumulativeRank' => $secondSemesterResult?->cumulative_rank,
            'isComplete' => $secondSemesterResult !== null && $firstSemesterResult !== null
                && $secondSemesterResult->cumulative_average_percentage !== null,
            'publishedAt' => $secondSemesterResult?->published_at ?? $firstSemesterResult?->published_at,
        ];
    }

    /** @return array<int, float> */
    private function subjectScores(?Result $result): array
    {
        if (! $result) return [];

        $result->loadMissing([
            'exam.subjects.subject',
            'exam.subjects.components.marks' => fn ($query) => $query->where('student_id', $result->student_id)->where('status', 'verified'),
        ]);

        $scores = [];
        foreach ($result->exam->subjects as $examSubject) {
            $weightedScore = 0.0;
            foreach ($examSubject->components as $component) {
                $mark = $component->marks->first();
                if ($mark && (float) $component->max_marks > 0) {
                    $weightedScore += ((float) $mark->marks_obtained / (float) $component->max_marks) * (float) $component->weight;
                }
            }
            $scores[$examSubject->subject_id] = round($weightedScore, 2);
        }

        return $scores;
    }
}
