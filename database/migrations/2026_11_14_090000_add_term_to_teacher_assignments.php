<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teacher_assignments', 'term_id')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->foreignId('term_id')->nullable()->after('section_id')->constrained()->restrictOnDelete();
            });
        }

        foreach (['teacher_profile_id', 'section_id', 'subject_id'] as $column) {
            if (! Schema::hasIndex('teacher_assignments', [$column])) {
                Schema::table('teacher_assignments', function (Blueprint $table) use ($column): void {
                    $table->index($column, 'teacher_assignments_'.$column.'_supporting_index');
                });
            }
        }

        if (Schema::hasIndex('teacher_assignments', 'teacher_section_subject_unique')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->dropUnique('teacher_section_subject_unique');
            });
        }

        if (! Schema::hasIndex('teacher_assignments', 'teacher_section_subject_term_unique')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->unique(
                    ['teacher_profile_id', 'section_id', 'subject_id', 'term_id'],
                    'teacher_section_subject_term_unique',
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('teacher_assignments', 'teacher_section_subject_term_unique')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->dropUnique('teacher_section_subject_term_unique');
            });
        }

        if (Schema::hasColumn('teacher_assignments', 'term_id')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('term_id');
            });
        }

        if (! Schema::hasIndex('teacher_assignments', 'teacher_section_subject_unique')) {
            Schema::table('teacher_assignments', function (Blueprint $table): void {
                $table->unique(['teacher_profile_id', 'section_id', 'subject_id'], 'teacher_section_subject_unique');
            });
        }
    }
};
