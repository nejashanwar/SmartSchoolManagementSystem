<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Result;
use App\Models\Term;

class InvalidateDraftTermResults
{
    public function fromExam(Exam $exam): void
    {
        $term = $exam->term;
        if (! $term) return;

        $affectedTermIds = Term::query()
            ->where('academic_year_id', $term->academic_year_id)
            ->where('sort_order', '>=', $term->sort_order)
            ->pluck('id');
        $examIds = Exam::query()
            ->where('section_id', $exam->section_id)
            ->whereIn('term_id', $affectedTermIds)
            ->pluck('id');

        Result::query()->whereIn('exam_id', $examIds)->where('status', 'draft')->delete();
    }
}
