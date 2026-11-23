<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->dateTime('starts_at_datetime')->nullable();
            $table->dateTime('ends_at_datetime')->nullable();
        });

        foreach (DB::table('attendance_sessions')->get(['id', 'session_date', 'starts_at', 'ends_at']) as $session) {
            DB::table('attendance_sessions')->where('id', $session->id)->update([
                'starts_at_datetime' => $session->starts_at
                    ? CarbonImmutable::parse($session->session_date.' '.$session->starts_at)->format('Y-m-d H:i:s')
                    : null,
                'ends_at_datetime' => $session->ends_at
                    ? CarbonImmutable::parse($session->session_date.' '.$session->ends_at)->format('Y-m-d H:i:s')
                    : null,
            ]);
        }

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->dropColumn(['starts_at', 'ends_at']);
        });

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->renameColumn('starts_at_datetime', 'starts_at');
            $table->renameColumn('ends_at_datetime', 'ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->time('starts_at_time')->nullable();
            $table->time('ends_at_time')->nullable();
        });

        foreach (DB::table('attendance_sessions')->get(['id', 'starts_at', 'ends_at']) as $session) {
            DB::table('attendance_sessions')->where('id', $session->id)->update([
                'starts_at_time' => $session->starts_at
                    ? CarbonImmutable::parse($session->starts_at)->format('H:i:s')
                    : null,
                'ends_at_time' => $session->ends_at
                    ? CarbonImmutable::parse($session->ends_at)->format('H:i:s')
                    : null,
            ]);
        }

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->dropColumn(['starts_at', 'ends_at']);
        });

        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->renameColumn('starts_at_time', 'starts_at');
            $table->renameColumn('ends_at_time', 'ends_at');
        });
    }
};
