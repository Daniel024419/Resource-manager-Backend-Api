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
        Schema::create('time_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employeeId')->constrained('employees')->onDelete('cascade');
            $table->foreignId('employeeProjectId')->constrained('employee_projects')->onDelete('cascade');
            $table->string('task');
            $table->date('date');
            $table->time('startTime');
            $table->time('endTime');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_trackings');
    }
};
