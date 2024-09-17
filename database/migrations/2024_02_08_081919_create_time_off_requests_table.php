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
        Schema::create('time_off_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('refId')->unique();
            $table->unsignedBigInteger('userId');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->unsignedBigInteger('type');
            $table->text('details')->nullable();
            $table->string('proof')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewedBy')->nullable();
            $table->unsignedBigInteger('canBeReviewedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('canBeReviewedBy')->references('id')->on('users');
            $table->foreign('reviewedBy')->references('id')->on('users');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type')->references('id')->on('time_off_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_off_requests');
    }
};