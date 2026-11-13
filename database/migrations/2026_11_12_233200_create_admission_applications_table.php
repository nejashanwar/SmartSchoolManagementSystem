<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 24)->unique();
            $table->string('student_name');
            $table->string('guardian_name');
            $table->string('guardian_phone', 40);
            $table->string('guardian_email')->nullable();
            $table->string('previous_school')->nullable();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->text('review_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
