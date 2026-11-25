<?php

namespace App\Http\Controllers;

use App\Filament\Exports\AdmissionApplicationExporter;
use App\Models\AdmissionApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdmissionApplicationReportController extends Controller
{
    public function show(Request $request): View
    {
        Gate::authorize('export', AdmissionApplication::class);

        $validated = $this->validateReportRequest($request);
        $filters = $validated['filters'] ?? [];
        $search = $validated['search'] ?? null;

        return view('admissions.admin-report', [
            'applications' => $this->reportQuery($filters, $search)->get(),
            'search' => $search,
            'generatedAt' => now(),
            'excelUrl' => route('admin.admission-applications.report.xlsx', [
                'filters' => $filters,
                'search' => $search,
            ], false),
        ]);
    }

    public function downloadXlsx(Request $request): BinaryFileResponse
    {
        Gate::authorize('export', AdmissionApplication::class);

        $validated = $this->validateReportRequest($request);
        $query = $this->reportQuery($validated['filters'] ?? [], $validated['search'] ?? null);
        $columnMap = collect(AdmissionApplicationExporter::getVisibleColumns())
            ->mapWithKeys(fn (ExportColumn $column): array => [$column->getName() => (string) $column->getLabel()])
            ->all();
        $export = new Export;
        $export->exporter = AdmissionApplicationExporter::class;
        $exporter = $export->getExporter($columnMap, []);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'admission-report-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary Excel report file.');
        }

        $writer = new Writer;
        $writerOpened = false;
        $keepFileForResponse = false;

        try {
            $writer->openToFile($temporaryPath);
            $writerOpened = true;
            $writer->addRow(Row::fromValues(
                array_values($columnMap),
                $exporter->getXlsxHeaderCellStyle(),
            ));

            $query->chunk(250, function (EloquentCollection $applications) use ($writer, $exporter): void {
                foreach ($applications as $application) {
                    $writer->addRow(Row::fromValues(
                        $exporter($application),
                        $exporter->getXlsxCellStyle(),
                    ));
                }
            });

            $writer->close();
            $writerOpened = false;

            $response = response()->download($temporaryPath, 'admission-applications.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            $keepFileForResponse = true;

            return $response;
        } finally {
            if ($writerOpened) {
                $writer->close();
            }

            if (! $keepFileForResponse && is_file($temporaryPath) && ! unlink($temporaryPath)) {
                throw new RuntimeException('Unable to remove the temporary Excel report file.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateReportRequest(Request $request): array
    {
        return $request->validate([
            'filters' => ['nullable', 'array:status,grade,academic_year_id,submitted_at'],
            'filters.status' => ['nullable', 'array:value'],
            'filters.status.value' => [
                'nullable',
                Rule::in([
                    AdmissionApplication::STATUS_PENDING,
                    AdmissionApplication::STATUS_CORRECTION_REQUESTED,
                    AdmissionApplication::STATUS_APPROVED,
                    AdmissionApplication::STATUS_REJECTED,
                ]),
            ],
            'filters.grade' => ['nullable', 'array:value'],
            'filters.grade.value' => ['nullable', 'integer', 'exists:grades,id'],
            'filters.academic_year_id' => ['nullable', 'array:value'],
            'filters.academic_year_id.value' => ['nullable', 'integer', 'exists:academic_years,id'],
            'filters.submitted_at' => ['nullable', 'array:from,until'],
            'filters.submitted_at.from' => ['nullable', 'date_format:Y-m-d'],
            'filters.submitted_at.until' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function reportQuery(array $filters, ?string $search): Builder
    {
        $query = AdmissionApplication::query()
            ->with(['grade', 'academicYear', 'reviewer'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);
        $this->applySearch($query, $search);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $status = data_get($filters, 'status.value');
        $gradeId = data_get($filters, 'grade.value');
        $academicYearId = data_get($filters, 'academic_year_id.value');
        $submittedFrom = data_get($filters, 'submitted_at.from');
        $submittedUntil = data_get($filters, 'submitted_at.until');

        if (filled($status)) {
            $query->where('status', $status);
        }

        if (filled($gradeId)) {
            $query->where('grade_id', $gradeId);
        }

        if (filled($academicYearId)) {
            $query->where('academic_year_id', $academicYearId);
        }

        if (filled($submittedFrom)) {
            $query->whereDate('submitted_at', '>=', $submittedFrom);
        }

        if (filled($submittedUntil)) {
            $query->whereDate('submitted_at', '<=', $submittedUntil);
        }
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('reference_number', 'like', "%{$search}%")
                ->orWhere('student_name', 'like', "%{$search}%")
                ->orWhere('guardian_name', 'like', "%{$search}%");
        });
    }
}
