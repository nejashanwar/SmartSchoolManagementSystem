<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersTableFilterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_filter_users_by_active_status(): void
    {
        $activeUser = User::factory()->create(['name' => 'Active account', 'is_active' => true]);
        $inactiveUser = User::factory()->create(['name' => 'Inactive account', 'is_active' => false]);
        $admin = User::factory()->create(['is_active' => true]);
        $adminRole = Role::findOrCreate('super_admin', 'web');
        $adminRole->givePermissionTo(Permission::findOrCreate('ViewAny:User', 'web'));
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$activeUser, $inactiveUser])
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$activeUser])
            ->assertCanNotSeeTableRecords([$inactiveUser]);
    }
}
