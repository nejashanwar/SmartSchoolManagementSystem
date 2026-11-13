<?php

namespace App\Filament\Resources\AdmissionApplications\Pages;

use App\Filament\Resources\AdmissionApplications\AdmissionApplicationResource;
use App\Models\AdmissionApplication;
use App\Services\ReviewAdmissionApplication;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Auth\Access\AuthorizationException;

class ViewAdmissionApplication extends ViewRecord
{
    protected static string $resource = AdmissionApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->reviewAction('approve', 'Approve application', AdmissionApplication::STATUS_APPROVED),
            $this->reviewAction('requestCorrection', 'Request correction', AdmissionApplication::STATUS_CORRECTION_REQUESTED),
            $this->reviewAction('reject', 'Reject application', AdmissionApplication::STATUS_REJECTED),
        ];
    }

    private function reviewAction(string $name, string $label, string $decision): Action
    {
        return Action::make($name)
            ->label($label)
            ->color(match ($decision) {
                AdmissionApplication::STATUS_APPROVED => 'success',
                AdmissionApplication::STATUS_REJECTED => 'danger',
                default => 'warning',
            })
            ->visible(fn (AdmissionApplication $record): bool => ! in_array($record->status, [
                AdmissionApplication::STATUS_APPROVED,
                AdmissionApplication::STATUS_REJECTED,
            ], true))
            ->authorize(fn (): bool => auth()->user()?->can('Review:AdmissionApplication') ?? false)
            ->schema([
                Textarea::make('reason')
                    ->label('Decision reason')
                    ->required()
                    ->maxLength(2000),
            ])
            ->requiresConfirmation()
            ->action(function (AdmissionApplication $record, array $data) use ($decision): void {
                $reviewer = auth()->user();

                if (! $reviewer instanceof \App\Models\User) {
                    throw new AuthorizationException;
                }

                $result = app(ReviewAdmissionApplication::class)->handle($record, $decision, $data['reason'], $reviewer);

                $notification = Notification::make()
                    ->success()
                    ->title('Application decision saved');

                if ($decision === AdmissionApplication::STATUS_APPROVED) {
                    $message = 'Student account created. Username/admission number: '
                        .$result['username'].' · Temporary password: '.$result['temporary_password']
                        .'. Share these credentials securely; the password will not be shown again.';

                    if ($result['parent_username'] && $result['parent_temporary_password']) {
                        $message .= ' Parent account: '.$result['parent_username'].' · Temporary password: '.$result['parent_temporary_password'].'.';
                    }
                    if ($result['parent_account_conflict']) {
                        $message .= ' The guardian email already belongs to a non-parent account; create or link a PARENT account under Parent accounts.';
                    }

                    $notification->body($message);
                }

                $notification->send();
            });
    }
}
