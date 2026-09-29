<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tr_program_budget', function (Blueprint $table) {
            $table->string('budget_stage', 20)->nullable()->after('budget_allocation');
        });

        // Baris terlama tiap program dianggap pagu awal (Murni), sisanya Perubahan.
        // Baris terhapus dilewati supaya yang dilabeli sama dengan yang tampil di list.
        $program_ids = DB::table('tr_program_budget')->whereNull('deleted_at')->distinct()->pluck('program_id');
        foreach ($program_ids as $program_id) {
            $ids = DB::table('tr_program_budget')
                ->where('program_id', $program_id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->pluck('id');

            DB::table('tr_program_budget')
                ->where('id', $ids->first())
                ->update(['budget_stage' => 'Murni']);

            DB::table('tr_program_budget')
                ->whereIn('id', $ids->slice(1))
                ->update(['budget_stage' => 'Perubahan']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_program_budget', function (Blueprint $table) {
            $table->dropColumn('budget_stage');
        });
    }
};
