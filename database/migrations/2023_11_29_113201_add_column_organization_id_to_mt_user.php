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
        Schema::table('mt_user', function (Blueprint $table) {
            $table->bigInteger('organization_id')->index()->default(0)->after('user_access_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_user', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });
    }
};
