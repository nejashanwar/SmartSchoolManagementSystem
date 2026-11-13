<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 8) as $grade) {
            Grade::query()->firstOrCreate(
                ['sort_order' => $grade],
                ['name' => "Grade {$grade}", 'is_active' => true],
            );
        }
    }
}
