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
        Schema::table('mt_program_template', function (Blueprint $table) {
            $table->bigInteger('budget_year_id')->default(0)->index()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_program_template', function (Blueprint $table) {
            $table->dropColumn('budget_year_id');
        });
    }
};
