<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('exam_marks', function (Blueprint $table): void { $table->id(); $table->foreignId('exam_subject_id')->constrained()->restrictOnDelete(); $table->foreignId('student_id')->constrained()->restrictOnDelete(); $table->foreignId('entered_by')->constrained('users')->restrictOnDelete(); $table->decimal('marks_obtained',8,2); $table->string('status',16)->default('draft'); $table->timestamps(); $table->unique(['exam_subject_id','student_id']); }); }
 public function down(): void { Schema::dropIfExists('exam_marks'); }
};
