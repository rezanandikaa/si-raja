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
        // Isian wajib di form Realisasi, ditampilkan di kolom "Sifat Bantuan".
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->text('sifat_bantuan')->default('')->after('target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->dropColumn('sifat_bantuan');
        });
    }
};
