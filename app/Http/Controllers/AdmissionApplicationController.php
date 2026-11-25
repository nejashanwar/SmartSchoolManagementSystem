<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\AuditEvent;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $countries = $this->africanCountryNames(app()->getLocale());
        asort($countries, SORT_NATURAL | SORT_FLAG_CASE);

        return view('admissions.create', [
            'academicYears' => $academicYears,
            'grades' => $grades,
            'countries' => $countries,
            'ethiopiaRegions' => config('ethiopia_administrative_divisions'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ethiopiaRegions = config('ethiopia_administrative_divisions');
        $isEthiopian = $request->input('nationality') === 'ET';
        $regionCode = $request->input('region_code');
        $region = is_string($regionCode) ? ($ethiopiaRegions[$regionCode] ?? null) : null;
        $zoneCode = $request->input('zone_code');
        $zone = is_array($region) && is_string($zoneCode)
            ? ($region['zones'][$zoneCode] ?? null)
            : null;

        $data = $request->validate([
            'student_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'required_without:student_name', 'string', 'max:100'],
            'father_name' => ['nullable', 'required_without:student_name', 'string', 'max:100'],
            'grandfather_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:10'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            'place_of_birth' => ['nullable', 'string', 'max:150'],
            'nationality' => ['nullable', 'string', 'max:60', Rule::in(config('african_country_codes'))],
            'region_code' => $isEthiopian
                ? ['required', 'string', Rule::in(array_keys($ethiopiaRegions))]
                : ['prohibited'],
            'zone_code' => $isEthiopian
                ? ['required', 'string', Rule::in(is_array($region) ? array_keys($region['zones']) : [])]
                : ['prohibited'],
            'woreda_code' => $isEthiopian
                ? ['required', 'string', Rule::in(is_array($zone) ? array_keys($zone['woredas']) : [])]
                : ['prohibited'],
            'kebele' => $isEthiopian
                ? ['nullable', 'string', 'max:100']
                : ['prohibited'],
            'address' => ['nullable', 'string', 'max:5000'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:40'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:25'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'previous_grade' => ['nullable', 'string', 'max:100'],
            'transfer_reason' => ['nullable', 'string', 'max:5000'],
            'health_notes' => ['nullable', 'string', 'max:5000'],
            'photo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'birth_certificate_path' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'extensions:pdf,jpg,jpeg,png', 'max:2048'],
            'previous_transcript_path' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'extensions:pdf,jpg,jpeg,png', 'max:2048'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
        ]);

        if (filled($data['nationality'] ?? null)) {
            $data['nationality'] = $this->africanCountryNames('en')[$data['nationality']];
        }

        if ($isEthiopian) {
            $selectedRegion = $ethiopiaRegions[$data['region_code']];
            $selectedZone = $selectedRegion['zones'][$data['zone_code']];

            $data['region'] = $selectedRegion['name'];
            $data['zone'] = $selectedZone['name'];
            $data['woreda'] = $selectedZone['woredas'][$data['woreda_code']];
        } else {
            $data['region'] = null;
            $data['zone'] = null;
            $data['woreda'] = null;
        }

        unset($data['region_code'], $data['zone_code'], $data['woreda_code']);

        if (filled($data['first_name'] ?? null) && filled($data['father_name'] ?? null)) {
            $studentName = collect([
                $data['first_name'],
                $data['father_name'],
                $data['grandfather_name'] ?? null,
            ])->filter()->implode(' ');

            if (mb_strlen($studentName) > 255) {
                throw ValidationException::withMessages([
                    'first_name' => 'The combined student name may not exceed 255 characters.',
                ]);
            }

            $data['student_name'] = $studentName;
        }

        $year = AcademicYear::query()->whereKey($data['academic_year_id'])->firstOrFail();
        $gradeIsActive = Grade::query()->whereKey($data['grade_id'])->where('is_active', true)->exists();

        abort_unless($year->isAdmissionOpen() && $gradeIsActive, 403, 'Admission registration is closed.');

        foreach (['photo_path', 'birth_certificate_path', 'previous_transcript_path'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->storePrivateUpload($request->file($field));
            }
        }

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

    public function downloadDocument(AdmissionApplication $application, string $field): StreamedResponse
    {
        Gate::authorize('view', $application);

        $path = match ($field) {
            'photo_path' => $application->photo_path,
            'birth_certificate_path' => $application->birth_certificate_path,
            'previous_transcript_path' => $application->previous_transcript_path,
            default => null,
        };

        abort_unless(
            is_string($path)
                && dirname($path) === 'admission-applications'
                && Storage::disk('local')->exists($path),
            404,
        );

        return Storage::disk('local')->download($path);
    }

    private function storePrivateUpload(UploadedFile $file): string
    {
        $path = $file->store('admission-applications', 'local');

        if (! is_string($path)) {
            throw new RuntimeException('Unable to store an admission application attachment.');
        }

        return $path;
    }

    /**
     * @return array<string, string>
     */
    private function africanCountryNames(string $locale): array
    {
        $countryNames = iterator_to_array(
            \ResourceBundle::create($locale, 'ICUDATA-region')->get('Countries'),
        );

        return array_intersect_key($countryNames, array_flip(config('african_country_codes')));
    }

    public function submitted(string $reference): View
    {
        $application = AdmissionApplication::query()
            ->where('reference_number', $reference)
            ->firstOrFail();

        return view('admissions.submitted', ['reference' => $application->reference_number]);
    }
}
