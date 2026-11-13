<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('parent_profiles',function(Blueprint $table):void{$table->id();$table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();$table->string('phone',40)->nullable();$table->timestamps();}); Schema::create('parent_student',function(Blueprint $table):void{$table->id();$table->foreignId('parent_profile_id')->constrained()->cascadeOnDelete();$table->foreignId('student_id')->constrained()->restrictOnDelete();$table->timestamps();$table->unique(['parent_profile_id','student_id']);$table->index('student_id');}); }
 public function down(): void { Schema::dropIfExists('parent_student');Schema::dropIfExists('parent_profiles'); }
};
