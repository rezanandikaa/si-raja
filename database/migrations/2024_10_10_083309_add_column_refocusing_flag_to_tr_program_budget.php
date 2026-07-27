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
        Schema::table('tr_program_budget', function (Blueprint $table) {
            $table->boolean('refocusing_flag')->default(0)->after('budget_allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_budget', function (Blueprint $table) {
            $table->dropColumn('refocusing_flag');
        });
    }
};
