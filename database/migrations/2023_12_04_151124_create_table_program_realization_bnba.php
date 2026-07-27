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
        Schema::create('tr_program_realization_bnba', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('program_realization_id')->index();
            $table->bigInteger('program_id')->index();
            $table->bigInteger('bnba_type_id')->index(); // individu / kk ambil ke sy_option
            $table->string("nik", 20); //"NIK" => "3602013112800005"
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
        Schema::dropIfExists('tr_program_realization_bnba');
    }
};
