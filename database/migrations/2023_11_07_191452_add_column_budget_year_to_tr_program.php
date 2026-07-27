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
        Schema::table('tr_program', function (Blueprint $table) {
            $table->bigInteger('budget_year_id')->index()->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program', function (Blueprint $table) {
            $table->dropColumn('budget_year_id');
        });
    }
};
