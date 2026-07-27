<?php

namespace App\Jobs;

use App\Models\System\Sy_import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class ImportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $datas;
    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct($datas, $id)
    {
        $this->datas = $datas;
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->datas as $data) {

            $data_create = [
                'data_id' => source_data_active(),
                'file_id' => $this->id,
                'data' => json_encode($data),
                'is_sync' => false,
            ];

            Sy_import::create($data_create);
        }
    }
}
