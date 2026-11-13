<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\AuditEvent;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionApplicationController extends Controller
{
    public function create(): View
    {
        $academicYears = AcademicYear::query()->where('is_active', true)->orderByDesc('starts_on')->get()
            ->filter(fn (AcademicYear $year): bool => $year->isAdmissionOpen());
        $grades = Grade::query()->where('is_active', true)->orderBy('sort_order')->get();

        if ($academicYears->isEmpty() || $grades->isEmpty()) {
            return view('admissions.closed');
        }

        return view('admissions.create', [
            'academicYears' => $academicYears,
            'grades' => $grades,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
        ]);

        $year = AcademicYear::query()->whereKey($data['academic_year_id'])->firstOrFail();
        $gradeIsActive = Grade::query()->whereKey($data['grade_id'])->where('is_active', true)->exists();

        abort_unless($year->isAdmissionOpen() && $gradeIsActive, 403, 'Admission registration is closed.');

        $application = AdmissionApplication::query()->create([
            ...$data,
            'reference_number' => AdmissionApplication::newReferenceNumber(),
            'status' => AdmissionApplication::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        AuditEvent::record('admission.submitted', $application, [
            'reference_number' => $application->reference_number,
            'grade' => $application->grade->name,
            'academic_year' => $application->academicYear->name,
        ]);

        return redirect()->route('admissions.submitted', ['reference' => $application->reference_number]);
    }

    public function submitted(string $reference): View
    {
        $application = AdmissionApplication::query()
            ->where('reference_number', $reference)
            ->firstOrFail();

        return view('admissions.submitted', ['reference' => $application->reference_number]);
    }
}
