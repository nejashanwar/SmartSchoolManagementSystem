<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('exam_types',function(Blueprint $table):void{$table->id();$table->string('name',80)->unique();$table->boolean('is_active')->default(true)->index();$table->timestamps();}); Schema::table('exams',function(Blueprint $table):void{$table->foreignId('exam_type_id')->nullable()->after('academic_year_id')->constrained('exam_types')->restrictOnDelete();}); Schema::create('grade_scales',function(Blueprint $table):void{$table->id();$table->string('label',20)->unique();$table->decimal('min_percentage',5,2);$table->decimal('max_percentage',5,2);$table->unsignedSmallInteger('sort_order')->unique();$table->boolean('is_active')->default(true)->index();$table->timestamps();}); }
 public function down(): void { Schema::dropIfExists('grade_scales');Schema::table('exams',function(Blueprint $table):void{$table->dropConstrainedForeignId('exam_type_id');});Schema::dropIfExists('exam_types'); }
};
