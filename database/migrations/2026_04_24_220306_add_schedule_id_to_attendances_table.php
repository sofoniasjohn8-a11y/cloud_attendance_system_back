<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop old unique constraint (one per user per day)
            $table->dropUnique(['user_id', 'work_date']);

            // Add schedule reference
            $table->foreignId('schedule_id')->nullable()->after('office_id')->constrained()->onDelete('set null');

            // New unique: one clock-in per user per schedule per day
            $table->unique(['user_id', 'work_date', 'schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'work_date', 'schedule_id']);
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
            $table->unique(['user_id', 'work_date']);
        });
    }
};
