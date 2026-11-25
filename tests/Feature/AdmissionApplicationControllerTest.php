<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdmissionApplicationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admission_form_renders_the_extended_student_and_health_fields(): void
    {
        $this->openAdmissionData();

        $response = $this->get(route('admissions.create'));

        $response->assertOk()
            ->assertSee('Student information')
            ->assertSee('Health notes')
            ->assertSee('Birth certificate')
            ->assertSee('value="ET"', false)
            ->assertDontSee('value="US"', false)
            ->assertSee('name="region_code"', false)
            ->assertSee('name="kebele"', false)
            ->assertDontSee('name="address"', false)
            ->assertDontSee('name="place_of_birth"', false);
    }

    public function test_application_submission_saves_personal_details_and_private_documents(): void
    {
        Storage::fake('local');
        $admission = $this->openAdmissionData();
        $certificate = UploadedFile::fake()->create('birth-certificate.pdf', 100, 'application/pdf');

        $response = $this->post(route('admissions.store'), [
            ...$admission,
            'first_name' => 'Amina',
            'father_name' => 'Abdi',
            'grandfather_name' => 'Hassan',
            'gender' => 'female',
            'date_of_birth' => '2015-04-03',
            'place_of_birth' => 'Addis Ababa',
            'nationality' => 'ET',
            'region_code' => 'ET04',
            'zone_code' => 'ET0401',
            'woreda_code' => 'ET040101',
            'kebele' => '03',
            'address' => 'Bole, Addis Ababa',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
            'guardian_email' => 'abdi@example.test',
            'guardian_relationship' => 'Father',
            'guardian_occupation' => 'Teacher',
            'guardian_address' => 'Bole, Addis Ababa',
            'emergency_contact_name' => 'Fatima Hassan',
            'emergency_contact_phone' => '+251911111111',
            'previous_school' => 'Example Primary School',
            'previous_grade' => 'Kindergarten',
            'transfer_reason' => 'Moving to this area',
            'health_notes' => 'No known allergies',
            'birth_certificate_path' => $certificate,
        ]);

        $application = AdmissionApplication::query()->sole();

        $response->assertRedirect(route('admissions.submitted', ['reference' => $application->reference_number]));
        $this->assertSame('Amina Abdi Hassan', $application->student_name);
        $this->assertSame('female', $application->gender);
        $this->assertSame('Bole, Addis Ababa', $application->address);
        $this->assertSame('Ethiopia', $application->nationality);
        $this->assertSame('Oromia', $application->region);
        $this->assertSame('West Wellega', $application->zone);
        $this->assertSame('Mana Sibu', $application->woreda);
        $this->assertSame('abdi@example.test', $application->guardian_email);
        $this->assertSame('Father', $application->guardian_relationship);
        $this->assertSame('Fatima Hassan', $application->emergency_contact_name);
        $this->assertSame('Example Primary School', $application->previous_school);
        $this->assertSame('No known allergies', $application->health_notes);
        $this->assertDatabaseHas('audit_events', [
            'event' => 'admission.submitted',
            'subject_id' => $application->id,
        ]);
        Storage::disk('local')->assertExists($application->birth_certificate_path);
    }

    public function test_invalid_admission_documents_are_rejected_without_saving_an_application(): void
    {
        Storage::fake('local');
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'first_name' => 'Amina',
            'father_name' => 'Abdi',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
            'birth_certificate_path' => UploadedFile::fake()->create('certificate.pdf', 100, 'text/plain'),
        ]);

        $response->assertInvalid([
            'birth_certificate_path' => 'The birth certificate path field must be a file of type: pdf, jpg, jpeg, png.',
        ]);
        $this->assertDatabaseCount('admission_applications', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_non_african_nationality_is_rejected(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'student_name' => 'Legacy Student Name',
            'nationality' => 'US',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $response->assertInvalid('nationality');
        $this->assertDatabaseCount('admission_applications', 0);
    }

    public function test_kebele_is_rejected_for_non_ethiopian_nationality(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'student_name' => 'Legacy Student Name',
            'nationality' => 'KE',
            'kebele' => 'Test Kebele',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $response->assertInvalid('kebele');
        $this->assertDatabaseCount('admission_applications', 0);
    }

    public function test_ethiopian_nationality_requires_all_administrative_levels(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'student_name' => 'Legacy Student Name',
            'nationality' => 'ET',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $response->assertInvalid(['region_code', 'zone_code', 'woreda_code']);
        $this->assertDatabaseCount('admission_applications', 0);
    }

    public function test_woreda_must_belong_to_the_selected_zone_and_region(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'student_name' => 'Legacy Student Name',
            'nationality' => 'ET',
            'region_code' => 'ET04',
            'zone_code' => 'ET0401',
            'woreda_code' => 'ET020101',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $response->assertInvalid('woreda_code');
        $this->assertDatabaseCount('admission_applications', 0);
    }

    public function test_legacy_student_name_submissions_remain_supported(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->post(route('admissions.store'), [
            ...$admission,
            'student_name' => 'Legacy Student Name',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $application = AdmissionApplication::query()->sole();

        $response->assertRedirect(route('admissions.submitted', ['reference' => $application->reference_number]));
        $this->assertSame('Legacy Student Name', $application->student_name);
    }

    public function test_composed_student_names_over_255_characters_are_rejected(): void
    {
        $admission = $this->openAdmissionData();

        $response = $this->from(route('admissions.create'))->post(route('admissions.store'), [
            ...$admission,
            'first_name' => str_repeat('A', 100),
            'father_name' => str_repeat('B', 100),
            'grandfather_name' => str_repeat('C', 100),
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
        ]);

        $response->assertInvalid([
            'first_name' => 'The combined student name may not exceed 255 characters.',
        ]);
        $this->assertDatabaseCount('admission_applications', 0);
    }

    public function test_user_without_admission_view_permission_cannot_download_private_documents(): void
    {
        Storage::fake('local');
        $application = $this->createApplicationWithBirthCertificate();
        $url = route('admin.admission-applications.download', [
            'application' => $application,
            'field' => 'birth_certificate_path',
        ]);

        $this->actingAs(User::factory()->create())
            ->get($url)
            ->assertForbidden();
    }

    public function test_user_with_admission_view_permission_can_download_private_documents(): void
    {
        Storage::fake('local');
        $application = $this->createApplicationWithBirthCertificate();
        $url = route('admin.admission-applications.download', [
            'application' => $application,
            'field' => 'birth_certificate_path',
        ]);
        $this->actingAs($this->userWithAdmissionViewPermission())
            ->get($url)
            ->assertDownload('birth-certificate.pdf');
    }

    public function test_private_document_download_rejects_paths_outside_the_admission_directory(): void
    {
        Storage::fake('local');
        $application = $this->createApplicationWithBirthCertificate();
        $application->update([
            'birth_certificate_path' => 'admission-applications/../../.env',
        ]);
        $url = route('admin.admission-applications.download', [
            'application' => $application,
            'field' => 'birth_certificate_path',
        ]);

        $this->actingAs($this->userWithAdmissionViewPermission())
            ->get($url)
            ->assertNotFound();
    }

    /**
     * @return array{grade_id: int, academic_year_id: int}
     */
    private function openAdmissionData(): array
    {
        $this->travelTo('2026-03-15 12:00:00');

        $academicYear = AcademicYear::query()->create([
            'name' => '2026-2027',
            'is_active' => true,
            'admission_opens_at' => now()->subDay(),
            'admission_closes_at' => now()->addDay(),
        ]);
        $grade = Grade::query()->create([
            'name' => 'Grade 1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [
            'grade_id' => $grade->id,
            'academic_year_id' => $academicYear->id,
        ];
    }

    private function createApplicationWithBirthCertificate(): AdmissionApplication
    {
        $application = AdmissionApplication::query()->create([
            ...$this->openAdmissionData(),
            'reference_number' => 'MII-2026-ABCDEFGH',
            'student_name' => 'Amina Abdi',
            'guardian_name' => 'Abdi Hassan',
            'guardian_phone' => '+251900000000',
            'status' => AdmissionApplication::STATUS_PENDING,
            'birth_certificate_path' => 'admission-applications/birth-certificate.pdf',
        ]);
        Storage::disk('local')->put($application->birth_certificate_path, '%PDF-1.4 test document');

        return $application;
    }

    private function userWithAdmissionViewPermission(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('View:AdmissionApplication', 'web'));

        return $user;
    }
}
