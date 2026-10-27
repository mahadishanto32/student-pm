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
        Schema::table('milestone_tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('key_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestone_tasks', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
