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
        Schema::create('mt_region', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->default(0)->index();
            $table->string('code', 20)->nullable(); // 36.02.18.0001
            $table->string('name', 100); // WARUNGGUNUNG
            $table->string('type', 20); // 1-PROVINSI, 2-KABUPATEN, 3-KECAMATAN, 4-DESA/KELURAHAN
            $table->bigInteger('created_by_id')->default(0)->index();
            $table->bigInteger('updated_by_id')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt_region');
    }
};
