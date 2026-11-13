<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendance_sessions', 'teacher_profile_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->foreignId('teacher_profile_id')
                    ->nullable()
                    ->after('section_id')
                    ->constrained('teacher_profiles')
                    ->restrictOnDelete();
            });
        }

        foreach (DB::table('attendance_sessions')->get(['id', 'section_id', 'created_by']) as $session) {
            $teacherProfileId = DB::table('teacher_profiles')
                ->where('user_id', $session->created_by)
                ->where('employment_status', 'active')
                ->whereExists(fn ($query) => $query->selectRaw('1')
                    ->from('teacher_assignments')
                    ->whereColumn('teacher_assignments.teacher_profile_id', 'teacher_profiles.id')
                    ->where('teacher_assignments.section_id', $session->section_id))
                ->value('id');

            if (! $teacherProfileId) {
                $recordingTeachers = DB::table('attendance_records')
                    ->join('teacher_profiles', 'teacher_profiles.user_id', '=', 'attendance_records.recorded_by')
                    ->join('teacher_assignments', function ($join) use ($session): void {
                        $join->on('teacher_assignments.teacher_profile_id', '=', 'teacher_profiles.id')
                            ->where('teacher_assignments.section_id', '=', $session->section_id);
                    })
                    ->where('attendance_records.attendance_session_id', $session->id)
                    ->distinct()
                    ->pluck('teacher_profiles.id');

                if ($recordingTeachers->count() === 1) {
                    $teacherProfileId = $recordingTeachers->first();
                }
            }

            if ($teacherProfileId) {
                DB::table('attendance_sessions')->where('id', $session->id)
                    ->update(['teacher_profile_id' => $teacherProfileId]);
            }
        }

        // MySQL uses the old composite unique index to enforce the section_id
        // foreign key because section_id is its leading column. Keep a plain
        // section_id index before replacing that unique key.
        if (! Schema::hasIndex('attendance_sessions', 'attendance_sessions_section_id_index')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->index('section_id', 'attendance_sessions_section_id_index');
            });
        }

        if (Schema::hasIndex('attendance_sessions', 'attendance_sessions_section_id_session_date_name_unique')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->dropUnique('attendance_sessions_section_id_session_date_name_unique');
            });
        }

        if (! Schema::hasIndex('attendance_sessions', 'attendance_sessions_teacher_section_date_name_unique')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->unique(
                    ['teacher_profile_id', 'section_id', 'session_date', 'name'],
                    'attendance_sessions_teacher_section_date_name_unique',
                );
            });
        }
    }

    public function down(): void
    {
        $hasDuplicateSessions = DB::table('attendance_sessions')
            ->select('section_id', 'session_date', 'name')
            ->groupBy('section_id', 'session_date', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateSessions) {
            throw new RuntimeException('Cannot remove teacher-specific attendance sessions while duplicate section/date/name sessions exist.');
        }

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->dropUnique('attendance_sessions_teacher_section_date_name_unique');
        });

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->unique(['section_id', 'session_date', 'name']);
            $table->dropIndex('attendance_sessions_section_id_index');
            $table->dropConstrainedForeignId('teacher_profile_id');
        });
    }
};
