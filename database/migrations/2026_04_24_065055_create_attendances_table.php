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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('restrict');
            $table->date('work_date')->index();
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->decimal('lat_in', 10, 8)->nullable();
            $table->decimal('lng_in', 11, 8)->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'half-day'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
