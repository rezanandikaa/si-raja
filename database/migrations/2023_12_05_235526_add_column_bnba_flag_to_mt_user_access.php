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
        Schema::table('mt_user_access', function (Blueprint $table) {
            $table->boolean('bnba_flag')->default(0)->after('user_access_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_user_access', function (Blueprint $table) {
            $table->dropColumn('bnba_flag');
        });
    }
};
