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
        // Isian bebas dari form Program, ditampilkan di kolom "Aktivitas Real".
        Schema::table('tr_program', function (Blueprint $table) {
            $table->string('aktivitas_real_langsung', 255)->nullable()->after('description');
            $table->string('aktivitas_real_tidak_langsung', 255)->nullable()->after('aktivitas_real_langsung');
            $table->string('aktivitas_real_penunjang', 255)->nullable()->after('aktivitas_real_tidak_langsung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program', function (Blueprint $table) {
            $table->dropColumn([
                'aktivitas_real_langsung',
                'aktivitas_real_tidak_langsung',
                'aktivitas_real_penunjang',
            ]);
        });
    }
};
