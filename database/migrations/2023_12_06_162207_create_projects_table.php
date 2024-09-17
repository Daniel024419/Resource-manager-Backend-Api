<?php

use App\Enums\ProjectType;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('projectId')->unique();
            $table->string('name');
            $table->string('projectCode')->unique();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->boolean('billable')->default(false);
            $table->string('details')->nullable();
            $table->enum('projectType', ['internal', 'external']);
            $table->string('startDate')->nullable();
            $table->string('endDate')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('createdBy')->references('id')->on('employees')->onDelete('set null');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};