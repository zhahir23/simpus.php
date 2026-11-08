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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->index();
            $table->string('student_number')->nullable()->unique();
            $table->string('study_program')->nullable();
            $table->unsignedSmallInteger('cohort')->nullable();
            $table->string('member_status')->default('active')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_number']);
            $table->dropIndex(['role']);
            $table->dropIndex(['member_status']);
            $table->dropColumn(['role', 'student_number', 'study_program', 'cohort', 'member_status']);
        });
    }
};
