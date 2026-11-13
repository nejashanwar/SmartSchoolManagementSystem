<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('exams', function (Blueprint $table): void { $table->id(); $table->foreignId('academic_year_id')->constrained()->restrictOnDelete(); $table->foreignId('term_id')->nullable()->constrained()->restrictOnDelete(); $table->foreignId('section_id')->constrained()->restrictOnDelete(); $table->string('name',120); $table->date('exam_date')->nullable(); $table->string('status',20)->default('draft'); $table->timestamps(); $table->unique(['section_id','term_id','name']); $table->index(['academic_year_id','exam_date']); }); }
 public function down(): void { Schema::dropIfExists('exams'); }
};
