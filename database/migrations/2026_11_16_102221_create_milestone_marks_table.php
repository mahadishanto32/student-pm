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
        Schema::create('milestone_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_milestone_id')->constrained('project_milestones')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->decimal('given_marks', 5, 2);
            $table->decimal('out_of', 5, 2)->default(20);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['project_milestone_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_marks');
    }
};
