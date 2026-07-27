<?php

namespace App\Console\Commands;

use App\Jobs\ImportCsvJob;
use App\Models\System\Sy_file;
use App\Repositories\System\FileRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baduyengine:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $file_repo;

    public function __construct(FileRepository $file_repo)
    {
        parent::__construct();
        $this->file_repo = $file_repo;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = Sy_file::where('is_sync', false)
            ->get();
        foreach ($records as $file) {
            $path = storage_path($file->path);

            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0); // Misalnya, jika baris pertama adalah header
            $csv->setDelimiter(';'); // Sesuaikan dengan delimiter yang benar

            $chunkSize = 10000; // Jumlah baris dalam setiap chunk
            $totalProcessed = 0;

            // Inisialisasi ProgressBar
            $progressBar = $this->output->createProgressBar();
            $progressBar->start();

            do {
                // Buat statement untuk mengambil batch berikutnya
                $statement = (new Statement())
                    ->offset($totalProcessed) // Memulai dari total data yang telah diproses
                    ->limit($chunkSize); // Batasi jumlah baris dalam setiap chunk

                $records = $statement->process($csv);

                $datas = [];
                // Iterasi dan proses data dalam batch (chunk)
                foreach ($records as $record) {
                    $data = $this->transformKeyValueToArray($record);
                    array_push($datas, $data);
                }

                try {
                    ImportCsvJob::dispatch($datas, $file->id);
                } catch (\Exception $err) {
                    Log::info($err->getMessage());
                }

                // Perbarui total data yang telah diproses
                $totalProcessed += $chunkSize;

                if (count($records) > 0) {
                    $progressBar->setProgress($totalProcessed);
                }

                if ($totalProcessed == count($records)){
                    $progressBar->finish();
                }

                // Cek apakah masih ada data berikutnya
            } while (count($records) > 0);

            $this->file_repo->updateRecord($file->id, ['is_sync' => true]);
            $msg = "Queue import was created for {$file->file_name_original}...";
            $this->info($msg);
        }
        if ($records->count() == 0) {
            $this->info('No file to import queue');
        } else {
            $this->info('All file was imported and already import');
        }
    }

    public function transformKeyValueToArray($values) : array {
        $data = [];
        foreach ($values as $key => $value) {
            $keyParts = explode(';', $key);
            $valueParts = explode(';', $value);

            if (count($keyParts) == count($valueParts)) {
                for ($i=0; $i < count($keyParts); $i++) {
                    $data[$keyParts[$i]] = $valueParts[$i];
                }
            } else {
                Log::info("Error: ". json_encode($value));
            }
        }
        return $data;
    }
}
