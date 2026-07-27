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
        Schema::create('sy_preference', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('key', 100);
            $table->text('value');
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
        Schema::dropIfExists('sy_preference');
    }
};
