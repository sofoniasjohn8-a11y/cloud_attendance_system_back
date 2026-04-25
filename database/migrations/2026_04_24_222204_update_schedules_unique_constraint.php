<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'day_of_week']);
            $table->unique(['user_id', 'day_of_week', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'day_of_week', 'start_time']);
            $table->unique(['user_id', 'day_of_week']);
        });
    }
};
