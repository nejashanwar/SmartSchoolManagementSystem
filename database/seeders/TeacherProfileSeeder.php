<?php

namespace Database\Seeders;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherProfileSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->role('TEACHER')->each(function (User $user): void {
            TeacherProfile::query()->firstOrCreate(
                ['user_id' => $user->getKey()],
                ['employment_status' => 'active'],
            );
        });
    }
}
