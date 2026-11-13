<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exam_assessment_components')) {
            Schema::create('exam_assessment_components', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
                $table->string('name', 80);
                $table->decimal('max_marks', 8, 2);
                $table->decimal('weight', 5, 2);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['exam_subject_id', 'name']);
            });
        }

        if (! Schema::hasColumn('exam_subjects', 'marks_status')) {
            Schema::table('exam_subjects', function (Blueprint $table): void {
                $table->string('marks_status', 16)->default('draft')->after('max_marks')->index();
                $table->timestamp('submitted_at')->nullable()->after('marks_status');
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
                $table->text('review_note')->nullable()->after('reviewed_by');
            });
        }

        if (! Schema::hasColumn('exam_marks', 'assessment_component_id')) {
            Schema::table('exam_marks', function (Blueprint $table): void {
                $table->foreignId('assessment_component_id')->nullable()->after('exam_subject_id')->constrained('exam_assessment_components')->restrictOnDelete();
            });
        }
        if (! Schema::hasIndex('exam_marks', 'exam_marks_exam_subject_id_index')) {
            Schema::table('exam_marks', function (Blueprint $table): void {
                // Preserve this foreign key's supporting index when replacing
                // the old (exam_subject_id, student_id) unique key.
                $table->index('exam_subject_id', 'exam_marks_exam_subject_id_index');
            });
        }

        foreach (DB::table('exam_subjects')->get(['id', 'max_marks']) as $examSubject) {
            $hasMarks = DB::table('exam_marks')->where('exam_subject_id', $examSubject->id)->exists();
            if (DB::table('exam_assessment_components')->where('exam_subject_id', $examSubject->id)->exists()) {
                $componentId = DB::table('exam_assessment_components')->where('exam_subject_id', $examSubject->id)->value('id');
                if ($hasMarks) {
                    DB::table('exam_marks')->where('exam_subject_id', $examSubject->id)->whereNull('assessment_component_id')
                        ->update(['assessment_component_id' => $componentId]);
                }
                continue;
            }
            $defaults = $hasMarks
                ? [['Overall mark', $examSubject->max_marks, 100]]
                : [['Assignment 1', 10, 10], ['Assignment 2', 10, 10], ['Quiz/Test', 10, 10], ['Mid-Term', 20, 20], ['Final Exam', 50, 50]];

            foreach ($defaults as $order => [$name, $maxMarks, $weight]) {
                $componentId = DB::table('exam_assessment_components')->insertGetId([
                    'exam_subject_id' => $examSubject->id,
                    'name' => $name,
                    'max_marks' => $maxMarks,
                    'weight' => $weight,
                    'sort_order' => $order + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($hasMarks) {
                    DB::table('exam_marks')->where('exam_subject_id', $examSubject->id)
                        ->update(['assessment_component_id' => $componentId]);
                }
            }
        }

        if (Schema::hasIndex('exam_marks', 'exam_marks_exam_subject_id_student_id_unique')) {
            Schema::table('exam_marks', function (Blueprint $table): void {
                $table->dropUnique('exam_marks_exam_subject_id_student_id_unique');
            });
        }
        if (! Schema::hasIndex('exam_marks', 'exam_marks_component_student_unique')) {
            Schema::table('exam_marks', function (Blueprint $table): void {
                $table->unique(['assessment_component_id', 'student_id'], 'exam_marks_component_student_unique');
            });
        }
    }

    public function down(): void
    {
        $hasDuplicateLegacyMarks = DB::table('exam_marks')
            ->select('exam_subject_id', 'student_id')
            ->groupBy('exam_subject_id', 'student_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateLegacyMarks) {
            throw new RuntimeException('Cannot remove assessment components while multiple component marks exist for the same student and subject.');
        }

        Schema::table('exam_marks', function (Blueprint $table): void {
            $table->dropUnique('exam_marks_component_student_unique');
            $table->unique(['exam_subject_id', 'student_id']);
            $table->dropIndex('exam_marks_exam_subject_id_index');
            $table->dropConstrainedForeignId('assessment_component_id');
        });

        Schema::table('exam_subjects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['marks_status', 'submitted_at', 'reviewed_at', 'review_note']);
        });

        Schema::dropIfExists('exam_assessment_components');
    }
};
