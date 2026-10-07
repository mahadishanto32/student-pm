<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks_distributions', function (Blueprint $table) {
            $table->foreignId('project_marks_id')
                ->constrained('project_marks')
                ->cascadeOnDelete();

            $table->enum('topic', [
                'Milestones',
                'Project Book',
                'Implementation and Result',
                'Presentation',
                'Video Resume',
            ]);

            $table->decimal('given_marks', 5, 2)->default(0);
            $table->decimal('out_of', 5, 2);

            $table->timestamps();

            // Each topic can appear only once per project_marks record
            $table->primary(['project_marks_id', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks_distributions');
    }
};
