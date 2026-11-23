<?php

namespace App\Filament\Resources\Results\Pages;

use App\Filament\Resources\Results\ResultResource;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Result;
use Filament\Resources\Pages\Page;

class ListResults extends Page
{
    protected static string $resource = ResultResource::class;

    protected string $view = 'filament.resources.results.pages.list-results';

    protected static ?string $title = 'Results & performance';

    public ?int $examId = null;

    public string $statusFilter = 'all';

    public string $search = '';

    public function mount(): void
    {
        $this->examId = Exam::query()->whereHas('results')->latest('id')->value('id');
    }

    protected function getViewData(): array
    {
        $exams = Exam::query()
            ->with(['section.grade', 'subjects.subject', 'term.academicYear.terms'])
            ->whereHas('results')
            ->latest('id')
            ->get();

        $exam = $exams->firstWhere('id', $this->examId);

        if (! $exam) {
            return [
                'exams' => $exams,
                'exam' => null,
                'results' => collect(),
                'subjectAverages' => [],
                'isFinalSemester' => false,
            ];
        }

        $resultsQuery = Result::query()
            ->with('student')
            ->where('exam_id', $exam->id)
            ->when(in_array($this->statusFilter, ['draft', 'published'], true), fn ($query) => $query->where('status', $this->statusFilter))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->whereHas('student', fn ($studentQuery) => $studentQuery
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%"));
            })
            ->orderByRaw('section_rank IS NULL')
            ->orderBy('section_rank')
            ->orderBy('student_id');

        $results = $resultsQuery->get();
        $exam->load(['subjects.components']);
        $studentIds = $results->pluck('student_id');
        $componentIds = $exam->subjects->flatMap(fn ($subject) => $subject->components->pluck('id'));

        $marks = ExamMark::query()
            ->whereIn('assessment_component_id', $componentIds)
            ->whereIn('student_id', $studentIds)
            ->where('status', 'verified')
            ->get(['student_id', 'assessment_component_id', 'marks_obtained'])
            ->keyBy(fn ($mark) => $mark->student_id.'-'.$mark->assessment_component_id);

        $subjectAverages = [];
        foreach ($results as $result) {
            foreach ($exam->subjects as $examSubject) {
                $weightedAverage = 0.0;
                $complete = $examSubject->components->isNotEmpty();

                foreach ($examSubject->components as $component) {
                    $mark = $marks->get($result->student_id.'-'.$component->id);
                    $maximum = (float) $component->max_marks;

                    if (! $mark || $maximum <= 0) {
                        $complete = false;
                        break;
                    }

                    $weightedAverage += ((float) $mark->marks_obtained / $maximum) * (float) $component->weight;
                }

                $subjectAverages[$result->student_id][$examSubject->id] = $complete ? round($weightedAverage, 2) : null;
            }
        }

        $isFinalSemester = $exam->term
            && (int) $exam->term->academicYear->terms->sortBy('sort_order')->last()?->id === (int) $exam->term_id;

        return compact('exams', 'exam', 'results', 'subjectAverages', 'isFinalSemester');
    }
}
