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
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('clientId')->unique();
            $table->string('name');
            $table->string('details')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('createdBy')->references('id')->on('employees')->onDelete('set null');


        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
