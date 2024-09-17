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
        Schema::create('project_histories', function (Blueprint $table) {
            $table->id();
            $table->string('refId');
            $table->unsignedBigInteger('projectId');
            $table->dateTime('newDate');
            $table->dateTime('oldDate');
            $table->text('reason');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('projectId')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('createdBy')->references('id')->on('employees')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_histories');
    }
};
