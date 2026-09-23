<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestone_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_milestone_id')
                  ->constrained('project_milestones')
                  ->cascadeOnDelete();
            $table->text('key_points');
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestone_tasks');
    }
};
