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
        Schema::create('mt_program_template', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50); // Nomenklatur
            $table->text('concern');
            $table->text('performance');
            $table->text('indicator');
            $table->string('measure', 100);
            $table->bigInteger('organization_id')->index();
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
        Schema::dropIfExists('mt_program_template');
    }
};
