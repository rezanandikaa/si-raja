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
        Schema::create('sy_file', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('data_id')->index();
            $table->string('batch_code')->unique();
            $table->string('file_name_original');
            $table->string('extension', 10);
            $table->string('type', 50);
            $table->bigInteger('size');
            $table->string('path');
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
        Schema::dropIfExists('sy_file');
    }
};
