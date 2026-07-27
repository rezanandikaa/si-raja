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
            $table->bigInteger('program_goal_id')->default(0)->index()->after('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt_program_template', function (Blueprint $table) {
            $table->dropColumn('program_goal_id');
        });
    }
};
