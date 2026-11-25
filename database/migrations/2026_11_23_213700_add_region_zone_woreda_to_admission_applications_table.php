<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->string('region', 150)->nullable()->after('nationality');
            $table->string('zone', 150)->nullable()->after('region');
            $table->string('woreda', 150)->nullable()->after('zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table): void {
            $table->dropColumn(['region', 'zone', 'woreda']);
        });
    }
};
