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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('refId')->unique();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('createdBy');
            $table->integer('groupableId')->unsigned();
            $table->string('groupableType');
            $table->timestamps();

            $table->foreign('createdBy')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
