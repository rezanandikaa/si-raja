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
        // Sifat bantuan pindah ke tr_program_sifat_bantuan (satu kesatuan per program,
        // bukan per triwulan).
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->dropColumn('sifat_bantuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->text('sifat_bantuan')->default('')->after('target');
        });
    }
};
