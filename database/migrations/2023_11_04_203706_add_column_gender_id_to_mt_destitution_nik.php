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
        Schema::table('mt_destitution_nik', function (Blueprint $table) {
            $table->bigInteger('gender_id')->default(0)->index()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_destitution_nik', function (Blueprint $table) {
            $table->dropColumn('gender_id');
        });
    }
};
