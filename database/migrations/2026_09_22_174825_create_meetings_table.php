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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            // Foreign key to projects table
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();

            $table->dateTime('meeting_date_and_time');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('supervisor_note')->nullable();

            // platform: physical | online (default physical)
            $table->enum('platform', ['physical', 'online'])->default('physical');

            // type: present | completed | upcoming (default upcoming)
            $table->enum('type', ['present', 'completed', 'upcoming'])->default('upcoming');

            $table->dateTime('tentative_next_meeting_date_and_time')->nullable();

            $table->timestamps(); // creates created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
