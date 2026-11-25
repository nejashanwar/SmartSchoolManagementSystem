<?php

namespace App\Providers;

use App\Models\AuditEvent;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContentCollapsible);
        });

        Event::listen(RoleAttachedEvent::class, function (RoleAttachedEvent $event): void {
            if (app()->runningInConsole()) {
                return;
            }

            $roles = collect($event->rolesOrIds)
                ->map(fn ($role): ?string => $role instanceof Role ? $role->name : Role::find($role)?->name)
                ->filter()
                ->values()
                ->all();

            if ($event->model instanceof User && in_array('TEACHER', $roles, true)) {
                $profile = TeacherProfile::query()->firstOrCreate(
                    ['user_id' => $event->model->getKey()],
                    ['employment_status' => 'active'],
                );

                if ($profile->wasRecentlyCreated) {
                    AuditEvent::record('teacher.profile_created', $profile, [
                        'user_id' => $event->model->getKey(),
                    ]);
                }
            }

            AuditEvent::record('user.roles_attached', $event->model, ['roles' => $roles]);
        });

        Event::listen(RoleDetachedEvent::class, function (RoleDetachedEvent $event): void {
            if (app()->runningInConsole()) {
                return;
            }

            $roles = collect($event->rolesOrIds)
                ->map(fn ($role): ?string => $role instanceof Role ? $role->name : Role::find($role)?->name)
                ->filter()
                ->values()
                ->all();

            AuditEvent::record('user.roles_detached', $event->model, ['roles' => $roles]);
        });

        Event::listen(PermissionAttachedEvent::class, function (PermissionAttachedEvent $event): void {
            $this->auditPermissionChange($event->model, $event->permissionsOrIds, 'attached');
        });

        Event::listen(PermissionDetachedEvent::class, function (PermissionDetachedEvent $event): void {
            $this->auditPermissionChange($event->model, $event->permissionsOrIds, 'detached');
        });
    }

    private function auditPermissionChange(object $model, mixed $permissionsOrIds, string $change): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $permissions = collect($permissionsOrIds)
            ->map(fn ($permission): ?string => $permission instanceof Permission ? $permission->name : Permission::find($permission)?->name)
            ->filter()
            ->values()
            ->all();

        $subject = $model instanceof Role ? 'role' : 'user';

        AuditEvent::record("{$subject}.permissions_{$change}", $model, [
            'permissions' => $permissions,
        ]);
    }
}
