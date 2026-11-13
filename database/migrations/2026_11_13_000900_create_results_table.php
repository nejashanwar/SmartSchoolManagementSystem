<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('results',function(Blueprint $table):void{$table->id();$table->foreignId('exam_id')->constrained()->restrictOnDelete();$table->foreignId('student_id')->constrained()->restrictOnDelete();$table->decimal('total_marks',10,2);$table->decimal('maximum_marks',10,2);$table->decimal('average_percentage',5,2);$table->string('grade_label',20)->nullable();$table->string('status',16)->default('draft')->index();$table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();$table->timestamp('published_at')->nullable();$table->timestamps();$table->unique(['exam_id','student_id']);$table->index(['student_id','status']);}); }
 public function down(): void { Schema::dropIfExists('results'); }
};
