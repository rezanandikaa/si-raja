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
        Schema::create('sy_import', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('data_id')->index();
            $table->bigInteger('file_id')->index();
            $table->json('data');
            $table->boolean('is_sync');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sy_import');
    }
};
