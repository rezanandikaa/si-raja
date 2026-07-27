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
        Schema::create('mt_user_access', function (Blueprint $table) {
            $table->id();
            $table->string('user_access_name', 100)->unique();
            $table->string('user_access_desc', 300);
            $table->json('access_module');
            $table->bigInteger('created_by_id')->default(0)->index();
            $table->bigInteger('updated_by_id')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt_user_access');
    }
};
