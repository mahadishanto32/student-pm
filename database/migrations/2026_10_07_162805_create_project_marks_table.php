<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_marks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            // users with role = teacher
            $table->foreignId('supervisor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // users with role = student
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('remarks')->nullable();

            $table->timestamps();

            // One mark record per student per project
            $table->unique(['project_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_marks');
    }
};
