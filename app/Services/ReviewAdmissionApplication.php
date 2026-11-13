<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AuditEvent;
use App\Models\Student;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role as PermissionRole;

class ReviewAdmissionApplication
{
    public function handle(AdmissionApplication $application, string $decision, string $reason, User $reviewer): array
    {
        if (! in_array($decision, [
            AdmissionApplication::STATUS_APPROVED,
            AdmissionApplication::STATUS_REJECTED,
            AdmissionApplication::STATUS_CORRECTION_REQUESTED,
        ], true)) {
            throw ValidationException::withMessages(['decision' => 'Choose a valid application decision.']);
        }

        return DB::transaction(function () use ($application, $decision, $reason, $reviewer): array {
            $locked = AdmissionApplication::query()->lockForUpdate()->findOrFail($application->getKey());

            if (in_array($locked->status, [AdmissionApplication::STATUS_APPROVED, AdmissionApplication::STATUS_REJECTED], true)) {
                throw ValidationException::withMessages(['decision' => 'This application has already been finalized.']);
            }

            $previousStatus = $locked->status;
            $temporaryPassword = null;
            $parentTemporaryPassword = null;
            $parentUsername = null;
            $parentAccountConflict = false;

            if ($decision === AdmissionApplication::STATUS_APPROVED) {
                $academicYear = $locked->academicYear;
                $admissionNumber = Student::newAdmissionNumber($academicYear);
                $temporaryPassword = Str::password(20);

                $studentUser = User::query()->create([
                    'name' => $locked->student_name,
                    'username' => $admissionNumber,
                    'email' => null,
                    'password' => $temporaryPassword,
                    'is_active' => true,
                    'must_change_password' => true,
                ]);
                $studentUser->assignRole(PermissionRole::findByName('STUDENT', 'web'));

                $student = Student::query()->create([
                    'user_id' => $studentUser->getKey(),
                    'admission_application_id' => $locked->getKey(),
                    'admission_number' => $admissionNumber,
                    'grade_id' => $locked->grade_id,
                    'academic_year_id' => $locked->academic_year_id,
                    'full_name' => $locked->student_name,
                    'status' => 'active',
                    'admitted_at' => now(),
                ]);

                AuditEvent::record('student.created', $student, [
                    'admission_number' => $admissionNumber,
                    'username' => $admissionNumber,
                    'grade_id' => $student->grade_id,
                    'academic_year_id' => $student->academic_year_id,
                    'application_reference' => $locked->reference_number,
                ]);

                if (filled($locked->guardian_email)) {
                    $parentUsername = strtolower(trim($locked->guardian_email));
                    $parentUser = User::query()->whereRaw('LOWER(email) = ?', [$parentUsername])->first();

                    if (! $parentUser) {
                        $parentTemporaryPassword = Str::password(20);
                        $parentUser = User::query()->create([
                            'name' => $locked->guardian_name,
                            'email' => $parentUsername,
                            'username' => null,
                            'password' => $parentTemporaryPassword,
                            'is_active' => true,
                            'must_change_password' => true,
                        ]);
                        $parentUser->assignRole(PermissionRole::findByName('PARENT', 'web'));
                    }

                    if ($parentUser->hasRole('PARENT')) {
                        $parentProfile = ParentProfile::query()->firstOrCreate(
                            ['user_id' => $parentUser->id],
                            ['phone' => $locked->guardian_phone],
                        );
                        $parentProfile->students()->syncWithoutDetaching([$student->id]);
                        AuditEvent::record('parent.student_linked', $parentProfile, ['student_id' => $student->id]);
                    } else {
                        $parentAccountConflict = true;
                        $parentUsername = null;
                        $parentTemporaryPassword = null;
                    }
                }
            }

            $locked->update([
                'status' => $decision,
                'review_reason' => $reason,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ]);

            AuditEvent::record('admission.reviewed', $locked, [
                'from' => $previousStatus,
                'to' => $decision,
                'reason' => $reason,
                'reference_number' => $locked->reference_number,
            ]);

            return [
                'application' => $locked->refresh()->load(['student.user']),
                'username' => $locked->student?->admission_number,
                'temporary_password' => $temporaryPassword,
                'parent_username' => $parentUsername,
                'parent_temporary_password' => $parentTemporaryPassword,
                'parent_account_conflict' => $parentAccountConflict,
            ];
        });
    }
}
