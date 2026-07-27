<?php

namespace App\Console\Commands;

use App\Models\Transaction\Tr_program;
use App\Repositories\Master\ProgramTemplateRepository;
use App\Repositories\Transaction\ProgramRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixProgram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baduyengine:fix-program {--option=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix program and organization';

    protected $program_template_repo;
    protected $program_repo;

    public function __construct(
        ProgramTemplateRepository $program_template_repo,
        ProgramRepository $program_repo
    ) {
        parent::__construct();
        $this->program_template_repo = $program_template_repo;
        $this->program_repo = $program_repo;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $opt = $this->option('option');

        $records = Tr_program::leftJoin('mt_program_template', 'tr_program.program_template_id', 'mt_program_template.id')
            ->where('mt_program_template.type', 'sub_activity')
            ->whereColumn('mt_program_template.organization_id', '!=', 'tr_program.organization_id')
            ->whereColumn('mt_program_template.budget_year_id', '=', 'tr_program.budget_year_id')
            ->when($opt != "null", function ($q) use ($opt) {
                $q->where('tr_program.budget_year_id', $opt);
            })
            ->select(
                'tr_program.*',
                'mt_program_template.organization_id as program_template_organization_id'
            )
            ->get();

        if ($records->count() == 0) {
            $this->info('No records found');
            return;
        }

        $bar = $this->output->createProgressBar(count($records));
        $bar->start();
        DB::beginTransaction();
        try {
            if ($records->count() > 0) {
                foreach ($records as $record) {
                    $data = [];
                    $program_template = $this->program_template_repo->getRecord($record->program_template_id);
                    if ($program_template) {
                        $data['sub_activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'concern', $record->budget_year_id);
                        $data['organization_id'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'organization_id', $record->budget_year_id);
                        $data['activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'activity', 'concern', $record->budget_year_id);
                        $data['program'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'program', 'concern', $record->budget_year_id);
                    }
                    $this->program_repo->updateRecord($record->id, $data);
                    Log::info('Fix Program: ' . json_encode($data));
                    $bar->advance();
                }
            }
            $bar->finish();
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::error($err->getMessage());
            $bar->finish();
        }
        $this->line('');
    }
}
