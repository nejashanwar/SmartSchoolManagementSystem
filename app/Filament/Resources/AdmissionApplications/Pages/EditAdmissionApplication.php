<?php

namespace App\Filament\Resources\AdmissionApplications\Pages;

use App\Filament\Resources\AdmissionApplications\AdmissionApplicationResource;
use App\Models\AdmissionApplication;
use App\Models\AuditEvent;
use App\Models\ParentProfile;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditAdmissionApplication extends EditRecord
{
    protected static string $resource = AdmissionApplicationResource::class;

    private bool $sectionAssignmentCleared = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $editableFields = [
            'student_name',
            'first_name',
            'father_name',
            'grandfather_name',
            'gender',
            'date_of_birth',
            'nationality',
            'region',
            'zone',
            'woreda',
            'kebele',
            'grade_id',
            'academic_year_id',
            'guardian_name',
            'guardian_phone',
            'guardian_email',
            'guardian_relationship',
            'guardian_occupation',
            'guardian_address',
            'emergency_contact_name',
            'emergency_contact_phone',
            'previous_school',
            'previous_grade',
            'transfer_reason',
            'health_notes',
        ];
        $data = array_intersect_key($data, array_flip($editableFields));
        $nationality = $data['nationality'] ?? null;

        if ($nationality === 'Ethiopia') {
            $this->validateEthiopianLocation($data);
        } elseif (filled($data['region'] ?? null)
            || filled($data['zone'] ?? null)
            || filled($data['woreda'] ?? null)
            || filled($data['kebele'] ?? null)) {
            throw ValidationException::withMessages([
                'nationality' => 'Ethiopian location details can only be used for Ethiopian nationality.',
            ]);
        } else {
            $data['region'] = null;
            $data['zone'] = null;
            $data['woreda'] = null;
            $data['kebele'] = null;
        }

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

        $this->validateGuardianAccountTarget($data['guardian_email'] ?? null);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->sectionAssignmentCleared = false;

        if (! $record instanceof AdmissionApplication) {
            return parent::handleRecordUpdate($record, $data);
        }

        return DB::transaction(function () use ($record, $data): Model {
            $previousGuardianEmail = $record->guardian_email;
            $application = parent::handleRecordUpdate($record, $data);
            $student = $application->student()
                ->with(['user', 'parentProfiles.user'])
                ->lockForUpdate()
                ->first();
            $parentSynced = false;

            if ($student instanceof Student) {
                $this->synchronizeStudent($student, $application);
                $parentSynced = $this->synchronizeParent($student, $application, $previousGuardianEmail);
            }

            $changedFields = array_values(array_diff(array_keys($application->getChanges()), ['updated_at']));

            if ($changedFields !== []) {
                AuditEvent::record('admission.details_updated', $application, [
                    'fields' => $changedFields,
                    'student_synced' => $student instanceof Student,
                    'parent_synced' => $parentSynced,
                    'section_assignment_cleared' => $this->sectionAssignmentCleared,
                ]);
            }

            return $application;
        });
    }

    protected function afterSave(): void
    {
        if ($this->sectionAssignmentCleared) {
            Notification::make()
                ->warning()
                ->title('Section assignment cleared')
                ->body('The student must be assigned to a section that matches the updated grade and academic year.')
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateEthiopianLocation(array $data): void
    {
        $region = collect(config('ethiopia_administrative_divisions'))
            ->first(fn (array $candidate): bool => $candidate['name'] === ($data['region'] ?? null));

        if (! is_array($region)) {
            throw ValidationException::withMessages(['region' => 'Choose a valid Ethiopian region.']);
        }

        $zone = collect($region['zones'])
            ->first(fn (array $candidate): bool => $candidate['name'] === ($data['zone'] ?? null));

        if (! is_array($zone)) {
            throw ValidationException::withMessages(['zone' => 'Choose a zone within the selected region.']);
        }

        if (! in_array($data['woreda'] ?? null, $zone['woredas'], true)) {
            throw ValidationException::withMessages(['woreda' => 'Choose a woreda within the selected zone.']);
        }
    }

    private function validateGuardianAccountTarget(?string $email): void
    {
        if (blank($email) || ! $this->getRecord()->student()->exists()) {
            return;
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])
            ->first();

        if ($user && ! $user->hasRole('PARENT')) {
            throw ValidationException::withMessages([
                'guardian_email' => 'This email belongs to a non-parent account and cannot be linked to the guardian.',
            ]);
        }
    }

    private function synchronizeStudent(Student $student, AdmissionApplication $application): void
    {
        $studentData = [
            'full_name' => $application->student_name,
            'grade_id' => $application->grade_id,
            'academic_year_id' => $application->academic_year_id,
        ];

        if ($student->section_id && ! Section::query()
            ->whereKey($student->section_id)
            ->where('grade_id', $application->grade_id)
            ->where('academic_year_id', $application->academic_year_id)
            ->exists()) {
            $studentData['section_id'] = null;
            $this->sectionAssignmentCleared = true;
        }

        $student->update($studentData);
        $student->user?->update(['name' => $application->student_name]);
    }

    private function synchronizeParent(Student $student, AdmissionApplication $application, ?string $previousGuardianEmail): bool
    {
        $profiles = $student->parentProfiles()->with('user')->get();
        $previousEmail = filled($previousGuardianEmail) ? mb_strtolower(trim($previousGuardianEmail)) : null;
        $currentProfile = $profiles->first(
            fn (ParentProfile $profile): bool => $previousEmail !== null
                && mb_strtolower((string) $profile->user?->email) === $previousEmail,
        );
        $newEmail = filled($application->guardian_email)
            ? mb_strtolower(trim($application->guardian_email))
            : null;

        if ($newEmail === null) {
            if ($currentProfile) {
                $student->parentProfiles()->detach($currentProfile->getKey());

                return true;
            }

            return false;
        }

        $targetUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$newEmail])
            ->first();

        if ($targetUser && ! $targetUser->hasRole('PARENT')) {
            throw ValidationException::withMessages([
                'guardian_email' => 'This email belongs to a non-parent account and cannot be linked to the guardian.',
            ]);
        }

        if ($targetUser) {
            $targetProfile = ParentProfile::query()->firstOrCreate(
                ['user_id' => $targetUser->getKey()],
                ['phone' => $application->guardian_phone],
            );

            if ($currentProfile && $currentProfile->isNot($targetProfile)) {
                $student->parentProfiles()->detach($currentProfile->getKey());
            }

            $targetUser->update(['name' => $application->guardian_name]);
            $targetProfile->update(['phone' => $application->guardian_phone]);
            $student->parentProfiles()->syncWithoutDetaching([$targetProfile->getKey()]);

            return true;
        }

        if ($currentProfile) {
            $currentProfile->user?->update([
                'name' => $application->guardian_name,
                'email' => $newEmail,
            ]);
            $currentProfile->update(['phone' => $application->guardian_phone]);

            return true;
        }

        return false;
    }
}
