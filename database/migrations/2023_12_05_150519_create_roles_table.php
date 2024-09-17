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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('can_add_user')->default(false);
            $table->boolean('can_add_manager')->default(false);
            $table->boolean('can_update_user_role')->default(false);
            $table->boolean('can_create_project')->default(false);
            $table->boolean('can_create_client')->default(false);
            $table->boolean('can_assign_user_to_project')->default(false);
            $table->boolean('can_assign_client_to_project')->default(false);
            $table->boolean('can_assign_user_to_department')->default(false);
            $table->boolean('can_assign_user_to_specialization')->default(false);
            $table->boolean('can_add_user_to_group')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};