<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (! Schema::hasColumn('results', 'cumulative_average_percentage')) {
                $table->decimal('cumulative_average_percentage', 5, 2)->nullable()->after('section_rank');
            }
            if (! Schema::hasColumn('results', 'cumulative_rank')) {
                $table->unsignedSmallInteger('cumulative_rank')->nullable()->after('cumulative_average_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('results', 'cumulative_average_percentage')) {
                $columns[] = 'cumulative_average_percentage';
            }
            if (Schema::hasColumn('results', 'cumulative_rank')) {
                $columns[] = 'cumulative_rank';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
