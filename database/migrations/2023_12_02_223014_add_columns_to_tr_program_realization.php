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
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->text('target')->default('')->after('budget_realization');
            $table->text('implementation_obstacle')->default('')->after('target');
            $table->text('benefit')->default('')->after('implementation_obstacle');
            $table->text('duration_note')->default('')->after('benefit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_realization', function (Blueprint $table) {
            $table->dropColumn(['duration_note','benefit','implementation_obstacle','target']);
        });
    }
};
