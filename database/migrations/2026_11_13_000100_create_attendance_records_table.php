<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('attendance_records', function (Blueprint $table): void { $table->id(); $table->foreignId('student_id')->constrained()->restrictOnDelete(); $table->foreignId('section_id')->constrained()->restrictOnDelete(); $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete(); $table->date('attendance_date'); $table->string('status', 16)->default('present'); $table->string('note', 500)->nullable(); $table->timestamps(); $table->unique(['student_id','attendance_date']); $table->index(['section_id','attendance_date']); }); }
 public function down(): void { Schema::dropIfExists('attendance_records'); }
};
