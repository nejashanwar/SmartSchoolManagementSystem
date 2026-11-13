<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AuditEvent;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-admin', function () {
    $name = trim((string) $this->ask('Administrator name'));
    $email = strtolower(trim((string) $this->ask('Administrator email')));

    $validator = Validator::make(
        ['name' => $name, 'email' => $email],
        ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email']],
    );

    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }

    $password = (string) $this->secret('Administrator password (at least 12 characters)');
    $confirmation = (string) $this->secret('Confirm password');

    if (mb_strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
        $this->error('The password must be at least 12 characters and both entries must match.');

        return 1;
    }

    $this->call('db:seed', ['--class' => \Database\Seeders\AccessControlSeeder::class]);

    $admin = User::query()->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'is_active' => true,
    ]);

    $admin->assignRole(Role::findByName('super_admin', 'web'));
    AuditEvent::record('user.created', $admin, [
        'name' => $admin->name,
        'email' => $admin->email,
        'roles' => ['super_admin'],
    ]);

    $this->info("Administrator account created: {$email}");

    return 0;
})->purpose('Create the initial administrator using an interactive, one-time process');
