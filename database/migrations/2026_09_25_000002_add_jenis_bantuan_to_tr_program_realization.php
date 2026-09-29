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
        // Isian bebas dari form Realisasi, tampil di kolom "Jenis Bantuan".
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->text('jenis_bantuan')->default('')->after('benefit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->dropColumn('jenis_bantuan');
        });
    }
};
