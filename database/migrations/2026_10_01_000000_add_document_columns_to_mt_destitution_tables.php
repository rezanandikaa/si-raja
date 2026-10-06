<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom yang ada di dokumen DTSEN tapi belum ada di tabel destitution.
 *
 * Dipakai DtsenDocumentMapper saat impor dan sebagai pilihan kolom grafik di
 * dashboard. Semua nullable supaya baris lama (kalau ada) tidak ikut gagal.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mt_destitution_nik', function (Blueprint $table) {
            $table->string('no_kk', 20)->nullable()->index()->after('nik');
            $table->string('rt', 10)->nullable()->after('address');
            $table->string('rw', 10)->nullable()->after('rt');
            $table->string('dusun', 100)->nullable()->after('rw');
            // status_hubungan_keluarga dari dokumen masih berupa angka tanpa
            // legend, jadi disimpan apa adanya; relationship_id dibiarkan 0.
            $table->bigInteger('relationship_code')->nullable()->after('relationship_id');
        });

        Schema::table('mt_destitution_kk', function (Blueprint $table) {
            $table->string('no_kk', 20)->nullable()->index()->after('nik');
            $table->bigInteger('member_count')->nullable()->after('address');
            $table->boolean('is_pbi_nas')->nullable()->after('is_kur');
            $table->boolean('is_pbi_pemda')->nullable()->after('is_pbi_nas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_destitution_nik', function (Blueprint $table) {
            $table->dropColumn(['no_kk', 'rt', 'rw', 'dusun', 'relationship_code']);
        });

        Schema::table('mt_destitution_kk', function (Blueprint $table) {
            $table->dropColumn(['no_kk', 'member_count', 'is_pbi_nas', 'is_pbi_pemda']);
        });
    }
};
