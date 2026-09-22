<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_book_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_book_id')
                  ->constrained('project_books')
                  ->cascadeOnDelete();
            $table->unsignedInteger('chapter_no');
            $table->string('chapter_title');
            $table->text('chapter_description')->nullable();
            $table->timestamps();

            $table->unique(['project_book_id', 'chapter_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_book_chapters');
    }
};