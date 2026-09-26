<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('done_by')->constrained('users')->cascadeOnDelete();
            $table->text('key_points')->nullable();
            $table->text('supervisor_feedback')->nullable();
            $table->date('date_of_presentation');
            $table->decimal('marks', 5, 2)->nullable();
            $table->string('presentation_file')->nullable();
            $table->enum('status', ['pending', 'completed', 'approved', 'rejected'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};