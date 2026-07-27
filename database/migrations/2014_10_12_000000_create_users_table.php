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
        Schema::create('mt_user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('gender', 1);
            $table->string('prefix_title', 100)->default('');
            $table->string('suffix_title', 100)->default('');
            $table->boolean('active_flag');
            $table->bigInteger('user_access_id')->default(0)->index();
            $table->rememberToken();
            $table->bigInteger('created_by_id')->default(0)->index();
            $table->bigInteger('updated_by_id')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt_user');
    }
};
