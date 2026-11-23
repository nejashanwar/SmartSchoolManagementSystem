<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('results', 'section_rank')) {
            Schema::table('results', function (Blueprint $table): void {
                $table->unsignedSmallInteger('section_rank')->nullable()->after('average_percentage')->index();
            });
        }

        foreach (DB::table('exams')->pluck('id') as $examId) {
            $position = 0;
            $rank = 0;
            $previousAverage = null;

            foreach (DB::table('results')->where('exam_id', $examId)
                ->orderByDesc('average_percentage')->orderBy('student_id')->get(['id', 'average_percentage']) as $result) {
                $position++;
                $average = number_format((float) $result->average_percentage, 2, '.', '');
                if ($average !== $previousAverage) {
                    $rank = $position;
                    $previousAverage = $average;
                }

                DB::table('results')->where('id', $result->id)->update(['section_rank' => $rank]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('results', 'section_rank')) {
            Schema::table('results', function (Blueprint $table): void {
                $table->dropIndex(['section_rank']);
                $table->dropColumn('section_rank');
            });
        }
    }
};
