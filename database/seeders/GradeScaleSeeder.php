<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        // Keep school-specific grade bands intact. These starter bands are only
        // inserted when the school has not configured any grade scale yet.
        if (GradeScale::query()->exists()) {
            return;
        }

        foreach ([
            ['label' => 'A', 'min_percentage' => 90, 'max_percentage' => 100, 'sort_order' => 1],
            ['label' => 'B', 'min_percentage' => 80, 'max_percentage' => 89.99, 'sort_order' => 2],
            ['label' => 'C', 'min_percentage' => 70, 'max_percentage' => 79.99, 'sort_order' => 3],
            ['label' => 'D', 'min_percentage' => 60, 'max_percentage' => 69.99, 'sort_order' => 4],
            ['label' => 'F', 'min_percentage' => 0, 'max_percentage' => 59.99, 'sort_order' => 5],
        ] as $band) {
            GradeScale::query()->create($band + ['is_active' => true]);
        }
    }
}
