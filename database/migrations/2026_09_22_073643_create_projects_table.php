<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('group_number')->unique();
            $table->string('project_name')->nullable();
            $table->string('project_topic')->nullable();
            $table->text('short_overview')->nullable();

            // Assigned teacher (single user)
            $table->foreignId('assigned_teacher')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->date('start_date');
            $table->date('tentative_end_date')->nullable();

            $table->enum('status', [
                'approved',
                'pending',
                'working',
                'completed',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
