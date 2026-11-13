<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('attendance_sessions', function (Blueprint $table): void { $table->id(); $table->foreignId('section_id')->constrained()->restrictOnDelete(); $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->date('session_date'); $table->string('name',80)->default('Daily'); $table->time('starts_at')->nullable(); $table->time('ends_at')->nullable(); $table->string('status',16)->default('open'); $table->timestamps(); $table->unique(['section_id','session_date','name']); $table->index(['session_date','status']); }); }
 public function down(): void { Schema::dropIfExists('attendance_sessions'); }
};
