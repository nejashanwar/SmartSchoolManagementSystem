<?php

namespace Tests\Feature;

use App\Filament\Exports\AdmissionApplicationExporter;
use App\Filament\Resources\AdmissionApplications\Pages\EditAdmissionApplication;
use App\Filament\Resources\AdmissionApplications\Pages\ListAdmissionApplications;
use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\Grade;
use App\Models\ParentProfile;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdmissionApplicationAdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_print_a_report_matching_search_and_table_filters(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $matching = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-MATCHING',
            'student_name' => 'Amina Abdi',
        ]);
        $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-SEARCHMISS',
            'student_name' => 'Other Student',
        ]);
        $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-STATUSMISS',
            'student_name' => 'Amina Bekele',
            'status' => AdmissionApplication::STATUS_APPROVED,
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->get(route('admin.admission-applications.report', [
                'filters' => ['status' => ['value' => AdmissionApplication::STATUS_PENDING]],
                'search' => 'Amina',
            ]))
            ->assertOk()
            ->assertSee('<table', false)
            ->assertSee($matching->reference_number)
            ->assertDontSee('MII-2026-SEARCHMISS')
            ->assertDontSee('MII-2026-STATUSMISS');
    }

    public function test_guest_is_redirected_before_printing_an_admission_report(): void
    {
        $this->get(route('admin.admission-applications.report'))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_user_without_application_view_permission_cannot_print_a_report(): void
    {
        $this->actingAs($this->userWithPermissions(['ViewAny:AdmissionApplication']))
            ->get(route('admin.admission-applications.report'))
            ->assertForbidden();
    }

    public function test_report_escapes_applicant_content(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $dangerousName = '<img src=x onerror=alert(1)>';
        $this->createAdmissionApplication($grade, $year, ['student_name' => $dangerousName]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->get(route('admin.admission-applications.report'))
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee($dangerousName, false);
    }

    public function test_report_search_treats_sql_syntax_as_literal_text(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $first = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-QUERY0001',
        ]);
        $second = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-QUERY0002',
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->get(route('admin.admission-applications.report', ['search' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertDontSee($first->reference_number)
            ->assertDontSee($second->reference_number);
    }

    public function test_report_rejects_unexpected_filter_keys(): void
    {
        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->getJson(route('admin.admission-applications.report', [
                'filters' => ['untrusted' => ['value' => 'reference_number']],
            ]))
            ->assertUnprocessable();
    }

    public function test_report_downloads_filtered_applications_as_excel(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EXCELMATCH',
            'student_name' => 'Amina Abdi',
        ]);
        $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EXCELMISS',
            'student_name' => 'Other Student',
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->get(route('admin.admission-applications.report.xlsx', [
                'filters' => ['status' => ['value' => AdmissionApplication::STATUS_PENDING]],
                'search' => 'Amina',
            ]))
            ->assertOk()
            ->assertDownload('admission-applications.xlsx');
    }

    public function test_user_without_application_view_permission_cannot_download_excel_report(): void
    {
        $this->actingAs($this->userWithPermissions(['ViewAny:AdmissionApplication']))
            ->get(route('admin.admission-applications.report.xlsx'))
            ->assertForbidden();
    }

    public function test_filtered_export_contains_only_matching_records_and_csv_xlsx_formats(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $matching = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EXPORTMATCH',
            'student_name' => 'Amina Abdi',
        ]);
        $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EXPORTMISS',
            'student_name' => 'Other Student',
        ]);
        $exports = ExportAction::fake();

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]));

        Livewire::test(ListAdmissionApplications::class)
            ->set('tableFilters.status.value', AdmissionApplication::STATUS_PENDING)
            ->set('tableFilters.grade.value', (string) $grade->getKey())
            ->set('tableSearch', 'Amina')
            ->callAction(TestAction::make('export')->table())
            ->assertHasNoFormErrors();

        $exports->assertDispatched(
            AdmissionApplicationExporter::class,
            function (Export $export, Builder $query, array $columnMap, array $options, array $formats) use ($matching): bool {
                $this->assertSame([$matching->getKey()], $query->pluck('id')->all());
                $this->assertSame([ExportFormat::Csv, ExportFormat::Xlsx], $formats);

                return true;
            },
        );
    }

    public function test_editing_an_approved_application_synchronizes_linked_student_and_parent_records(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $application = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EDIT0001',
            'status' => AdmissionApplication::STATUS_APPROVED,
            'review_reason' => 'Approved after review',
            'student_name' => 'Old Student Name',
            'first_name' => 'Old',
            'father_name' => 'Student',
            'guardian_name' => 'Old Guardian',
            'guardian_phone' => '+251900000000',
            'guardian_email' => 'old.guardian@example.test',
        ]);
        $studentUser = User::factory()->create([
            'name' => 'Old Student Name',
            'username' => 'MII-2026-ABCDEF',
        ]);
        $student = Student::query()->create([
            'user_id' => $studentUser->getKey(),
            'admission_application_id' => $application->getKey(),
            'admission_number' => 'MII-2026-ABCDEF',
            'grade_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'full_name' => 'Old Student Name',
            'status' => 'active',
        ]);
        $parentUser = User::factory()->create([
            'name' => 'Old Guardian',
            'email' => 'old.guardian@example.test',
        ]);
        $parentUser->assignRole(Role::findOrCreate('PARENT', 'web'));
        $parentProfile = ParentProfile::query()->create([
            'user_id' => $parentUser->getKey(),
            'phone' => '+251900000000',
        ]);
        $parentProfile->students()->attach($student->getKey());

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
            'Update:AdmissionApplication',
        ]));

        Livewire::test(EditAdmissionApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'student_name' => 'New Student Name',
                'first_name' => 'New',
                'father_name' => 'Student',
                'nationality' => 'Ethiopia',
                'region' => 'Oromia',
                'zone' => 'West Wellega',
                'woreda' => 'Mana Sibu',
                'kebele' => '04',
                'guardian_name' => 'New Guardian',
                'guardian_phone' => '+251911111111',
                'guardian_email' => 'new.guardian@example.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('admission_applications', [
            'id' => $application->getKey(),
            'student_name' => 'New Student Name',
            'nationality' => 'Ethiopia',
            'region' => 'Oromia',
            'zone' => 'West Wellega',
            'woreda' => 'Mana Sibu',
            'status' => AdmissionApplication::STATUS_APPROVED,
            'review_reason' => 'Approved after review',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->getKey(),
            'full_name' => 'New Student Name',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $studentUser->getKey(),
            'name' => 'New Student Name',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $parentUser->getKey(),
            'name' => 'New Guardian',
            'email' => 'new.guardian@example.test',
        ]);
        $this->assertDatabaseHas('parent_profiles', [
            'id' => $parentProfile->getKey(),
            'phone' => '+251911111111',
        ]);
    }

    public function test_editing_an_approved_students_grade_clears_an_incompatible_section(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $newGrade = Grade::query()->create([
            'name' => 'Grade 2',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $section = Section::query()->create([
            'grade_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => 'Grade 1 A',
            'capacity' => 30,
            'is_active' => true,
        ]);
        $application = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-EDIT0002',
            'status' => AdmissionApplication::STATUS_APPROVED,
        ]);
        $student = Student::query()->create([
            'user_id' => User::factory()->create()->getKey(),
            'admission_application_id' => $application->getKey(),
            'admission_number' => 'MII-2026-ABCDEF',
            'grade_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'section_id' => $section->getKey(),
            'full_name' => $application->student_name,
            'status' => 'active',
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
            'Update:AdmissionApplication',
        ]));

        Livewire::test(EditAdmissionApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm(['grade_id' => (string) $newGrade->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('students', [
            'id' => $student->getKey(),
            'grade_id' => $newGrade->getKey(),
            'section_id' => null,
        ]);
    }

    public function test_edit_rejects_an_ethiopian_woreda_outside_the_selected_zone(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $application = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-BADLOC01',
            'student_name' => 'Amina Student',
            'guardian_name' => 'Guardian',
            'guardian_phone' => '+251900000000',
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
            'Update:AdmissionApplication',
        ]));

        Livewire::test(EditAdmissionApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'nationality' => 'Ethiopia',
                'region' => 'Afar',
                'zone' => 'Awsi /Zone 1',
                'woreda' => 'Mana Sibu',
            ])
            ->call('save')
            ->assertHasFormErrors(['woreda']);

        $this->assertDatabaseHas('admission_applications', [
            'id' => $application->getKey(),
            'nationality' => null,
            'woreda' => null,
        ]);
    }

    public function test_user_without_update_permission_cannot_open_an_application_edit_page(): void
    {
        $year = $this->createAcademicYear();
        $grade = $this->createGrade();
        $application = $this->createAdmissionApplication($grade, $year, [
            'reference_number' => 'MII-2026-NOEDIT01',
        ]);

        $this->actingAs($this->userWithPermissions([
            'ViewAny:AdmissionApplication',
            'View:AdmissionApplication',
        ]))
            ->get(route('filament.admin.resources.admission-applications.edit', ['record' => $application]))
            ->assertForbidden();
    }

    private function createAcademicYear(): AcademicYear
    {
        return AcademicYear::query()->create([
            'name' => '2026-2027',
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-06-30',
            'admission_opens_at' => now()->subDay(),
            'admission_closes_at' => now()->addDay(),
            'is_active' => true,
        ]);
    }

    private function createGrade(): Grade
    {
        return Grade::query()->create([
            'name' => 'Grade 1',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function createAdmissionApplication(Grade $grade, AcademicYear $year, array $attributes = []): AdmissionApplication
    {
        return AdmissionApplication::query()->create(array_merge([
            'reference_number' => 'MII-2026-'.Str::upper(Str::random(8)),
            'student_name' => 'Test Student',
            'guardian_name' => 'Test Guardian',
            'guardian_phone' => '+251900000000',
            'grade_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'status' => AdmissionApplication::STATUS_PENDING,
            'submitted_at' => now(),
        ], $attributes));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('ADMIN', 'web'));
        $user->givePermissionTo(array_map(
            fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'),
            $permissions,
        ));

        return $user;
    }
}
