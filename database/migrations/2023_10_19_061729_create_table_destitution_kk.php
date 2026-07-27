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
        Schema::create('mt_destitution_kk', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('data_id')->index();
            $table->string("p3ke", 100)->nullable(); // "ID Keluarga P3KE" => "122237092"
            $table->bigInteger("last_update_year")->nullable(); // "Dimuktakhirkan Tahun" => "2021"
            $table->bigInteger("province_id")->index(); //"Provinsi" => "BANTEN"
            $table->bigInteger("regency_id")->index(); //"Kabupaten/Kota" => "LEBAK"
            $table->bigInteger("district_id")->index(); //"Kecamatan" => "MALINGPING"
            $table->bigInteger("subdistrict_id")->index(); //"Desa/Kelurahan" => "CILANGKAHAN"
            $table->string("kemdagri_code", 100)->nullable(); // "Kode Kemdagri" => "3602012002"
            $table->bigInteger("decile"); //"Desil Kesejahteraan" => "1"
            $table->bigInteger("percentile"); //"Persentil" => "5"
            $table->text("address")->nullable(); //"Alamat" => "KP. CIPINANG"
            $table->bigInteger("priority_verval_id")->index(); //"Prioritas Verval" => "Normal"
            $table->string("name", 100)->nullable(); //"Nama" => "IYUS"
            $table->string("nik", 20)->nullable(); //"NIK" => "3602013112800005"
            $table->bigInteger("padan_dukcapil_id")->index(); //"Padan Dukcapil" => "Padan"
            $table->string("gender", 1)->nullable(); //"Jenis Kelamin" => "Laki-laki"
            $table->dateTime("birth_date")->nullable(); //"Tanggal Lahir" => "1980-12-31"
            $table->bigInteger("job_id")->index(); //"Pekerjaan" => "Tidak/Belum Bekerja"
            $table->bigInteger("job_status_id")->index(); //"Status Pekerjaan" => "Kosong"
            $table->bigInteger("education_id")->index(); //"Pendidikan" => "Tdk Tamat SD/Sederajat"
            $table->bigInteger("marital_status_id")->index(); //"Status Kawin" => "Kawin""
            $table->bigInteger("home_ownership_id")->index();
            $table->bigInteger("etc_ownership_id")->index();
            $table->bigInteger("home_roof_id")->index();
            $table->bigInteger("home_roof_quality_id")->index();
            $table->bigInteger("home_wall_id")->index();
            $table->bigInteger("home_wall_quality_id")->index();
            $table->bigInteger("home_floor_id")->index();
            $table->bigInteger("home_floor_quality_id")->index();
            $table->bigInteger("home_electricity_id")->index();
            $table->bigInteger("home_electricity_power_id")->index();
            $table->bigInteger("home_cooking_id")->index();
            $table->bigInteger("home_water_id")->index();
            $table->bigInteger("home_toilet_ownership_id")->index();
            $table->bigInteger("stunting_risk_id")->index();
            $table->boolean("is_bpnt")->nullable(); //"Penerima BPNT" => "Tidak"
            $table->boolean("is_bpum")->nullable(); //"Penerima BPUM" => "Tidak"
            $table->boolean("is_bst")->nullable(); //"Penerima BST" => "Ya"
            $table->boolean("is_pkh")->nullable(); //"Penerima PKH" => "Tidak"
            $table->boolean("is_sembako")->nullable(); //"Penerima SEMBAKO" => "Tidak"
            $table->boolean("is_prakerja")->nullable(); //"Penerima Prakerja" => "Tidak"
            $table->boolean("is_kur")->nullable(); //"Penerima KUR" => "Tidak"
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
        Schema::dropIfExists('mt_destitution_kk');
    }
};
