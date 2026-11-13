<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('exam_subjects', function (Blueprint $table): void { $table->id(); $table->foreignId('exam_id')->constrained()->restrictOnDelete(); $table->foreignId('subject_id')->constrained()->restrictOnDelete(); $table->decimal('max_marks',8,2)->default(100); $table->timestamps(); $table->unique(['exam_id','subject_id']); }); }
 public function down(): void { Schema::dropIfExists('exam_subjects'); }
};
