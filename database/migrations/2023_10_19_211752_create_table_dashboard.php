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
        Schema::create('mt_dashboard', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('type', 50);
            $table->json('properties')->nullable();
            $table->boolean('active_flag');
            $table->bigInteger("created_by_id")->default(0)->index();
            $table->bigInteger("updated_by_id")->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt_dashboard');
    }
};
