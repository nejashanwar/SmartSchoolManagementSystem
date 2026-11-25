<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->string('first_name', 100)->nullable()->after('reference_number');
            $table->string('father_name', 100)->nullable()->after('first_name');
            $table->string('grandfather_name', 100)->nullable()->after('father_name');
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('nationality', 60)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('zone_woreda', 150)->nullable();
            $table->string('kebele', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_relationship', 50)->nullable();
            $table->string('guardian_occupation', 100)->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 25)->nullable();
            $table->string('previous_grade', 100)->nullable();
            $table->text('transfer_reason')->nullable();
            $table->string('birth_certificate_path')->nullable();
            $table->string('previous_transcript_path')->nullable();
            $table->text('health_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'father_name',
                'grandfather_name',
                'gender',
                'date_of_birth',
                'place_of_birth',
                'nationality',
                'photo_path',
                'zone_woreda',
                'kebele',
                'address',
                'guardian_relationship',
                'guardian_occupation',
                'guardian_address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'previous_grade',
                'transfer_reason',
                'birth_certificate_path',
                'previous_transcript_path',
                'health_notes',
            ]);
        });
    }
};
