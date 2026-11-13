<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['teacher_profile_id', 'section_id', 'subject_id'], 'teacher_section_subject_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('teacher_assignments'); }
};
