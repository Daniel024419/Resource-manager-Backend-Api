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
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('userId');
            $table->string('refId');
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('profilePicture')->nullable()->default('public/images/profile/8ysTH31LjoMeVWfVhNei7OC1NJVZTelYeyFCqDGF.png');
            $table->string('phoneNumber')->nullable();
            $table->string('location')->nullable();
            $table->string('timeZone')->nullable();
            $table->boolean('bookable')->default(true);
            $table->unsignedBigInteger('roleId')->nullable();
            $table->unsignedBigInteger('addedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();



            // Foreign key constraint
            $table->foreign('addedBy')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('roleId')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
