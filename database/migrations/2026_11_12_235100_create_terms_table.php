<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('name', 80);
            $table->unsignedSmallInteger('sort_order');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['academic_year_id', 'name']);
            $table->unique(['academic_year_id', 'sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('terms'); }
};
