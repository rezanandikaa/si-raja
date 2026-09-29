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
        // Isian bebas dari form Program, digabung dengan nama kecamatan di kolom "Lokasi" excel.
        Schema::table('tr_program', function (Blueprint $table) {
            $table->string('lokasi_tambahan', 255)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program', function (Blueprint $table) {
            $table->dropColumn('lokasi_tambahan');
        });
    }
};
