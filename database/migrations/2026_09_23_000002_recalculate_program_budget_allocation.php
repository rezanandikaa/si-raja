<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pagu program tadinya jumlah SEMUA baris tr_program_budget, jadi pagu lama
        // ikut terhitung dobel dengan pagu penggantinya. Sekarang pakai baris terakhir
        // tiap sumber pembiayaan, sama seperti recalculateBudgetAllocation().
        $program_ids = DB::table('tr_program_budget')->whereNull('deleted_at')->distinct()->pluck('program_id');

        foreach ($program_ids as $program_id) {
            $rows = DB::table('tr_program_budget')
                ->where('program_id', $program_id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get(['budget_source_id', 'budget_allocation']);

            $budget_allocation = 0;
            foreach ($rows->groupBy('budget_source_id') as $per_source) {
                $budget_allocation += (float) $per_source->last()->budget_allocation;
            }

            DB::table('tr_program')->where('id', $program_id)->update([
                'budget_allocation' => $budget_allocation,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perbaikan data, tidak bisa dibalik: nilai lama memang salah hitung.
    }
};
