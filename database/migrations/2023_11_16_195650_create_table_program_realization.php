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
        Schema::create('tr_program_realization', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('program_id')->index();
            $table->bigInteger('quarterly'); // triwulan
            $table->decimal('budget_allocation', 15, 2);
            $table->decimal('budget_realization', 15, 2);
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
        Schema::dropIfExists('tr_program_realization');
    }
};
