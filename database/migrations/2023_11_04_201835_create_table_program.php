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
        Schema::create('tr_program', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('program_goal_id')->index();
            $table->bigInteger('organization_id')->index();
            $table->string('code', 100);
            $table->string('name', 300);
            $table->string('sub_name', 300);
            $table->string('status', 50);
            $table->bigInteger('budget_source_id')->index();
            $table->decimal('budget_allocation', 15, 2);
            $table->decimal('budget_realization', 15, 2);
            $table->string('target', 300);
            $table->text('implementation_obstacle');
            $table->bigInteger('district_id')->index();
            $table->bigInteger('subdistrict_id')->index();
            $table->text('benefit');
            $table->text('duration_note');
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
        Schema::dropIfExists('tr_program');
    }
};
