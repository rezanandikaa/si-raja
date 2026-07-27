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
        Schema::create('sy_user_recovery', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->uuid('token');
            $table->dateTime('expired');
            $table->boolean('close_flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy_user_recovery');
    }
};
