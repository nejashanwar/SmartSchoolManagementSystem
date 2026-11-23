<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\TeacherAssignment;

class SyncExamSubjectsFromAssignments
{
    public function forAssignment(TeacherAssignment $assignment): void
    {
        $exams = Exam::query()->where('section_id', $assignment->section_id);

        if ($assignment->term_id) {
            $exams->where('term_id', $assignment->term_id);
        }

        foreach ($exams->get() as $exam) {
            $this->forExamAndSubject($exam, (int) $assignment->subject_id);
        }
    }

    public function forExam(Exam $exam): void
    {
        $assignments = TeacherAssignment::query()
            ->where('section_id', $exam->section_id)
            ->where('subject_id', '>', 0);

        if ($exam->term_id) {
            $assignments->where(function ($query) use ($exam): void {
                $query->where('term_id', $exam->term_id)->orWhereNull('term_id');
            });
        }

        foreach ($assignments->distinct()->pluck('subject_id') as $subjectId) {
            $this->forExamAndSubject($exam, (int) $subjectId);
        }
    }

    private function forExamAndSubject(Exam $exam, int $subjectId): void
    {
        if (in_array($exam->status, ['completed', 'published'], true)) {
            return;
        }

        ExamSubject::query()->firstOrCreate(
            ['exam_id' => $exam->id, 'subject_id' => $subjectId],
            ['max_marks' => 100],
        );
    }
}
