<?php

namespace App\Console\Commands;

use App\Jobs\ImportCsvJob;
use App\Libraries\SheetReader;
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
    protected $signature = 'baduyengine:import
        {--file-id= : Hanya proses berkas ini (untuk uji coba)}
        {--limit= : Batas jumlah baris per berkas; berkas tidak ditandai selesai}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $file_repo;
    protected $sheet_reader;

    public function __construct(FileRepository $file_repo, SheetReader $sheet_reader)
    {
        parent::__construct();
        $this->file_repo = $file_repo;
        $this->sheet_reader = $sheet_reader;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $records = Sy_file::where('is_sync', false)
            ->when($this->option('file-id'), function ($query, $file_id) {
                return $query->where('id', $file_id);
            })
            ->get();
        foreach ($records as $file) {
            $path = storage_path($file->path);

            // Berkas Excel dibaca streaming per baris (baris pertama = header),
            // supaya kolom JSON-nya sama dengan yang diharapkan SyncData.
            if (strtolower($file->extension) == 'xlsx') {
                // Satu berkas DTSEN bisa memuat sheet KK dan ART; yang dibaca
                // mengikuti tipe berkasnya, sama dengan pratinjau di halaman.
                $sheet_name = $file->type == 'INDIVIDU'
                    ? SheetReader::SHEET_ART
                    : SheetReader::SHEET_KK;

                $chunks = 0;
                $rows = 0;
                foreach ($this->sheet_reader->stream($path, $sheet_name) as $chunk) {
                    if ($limit !== null) {
                        $chunk = array_slice($chunk, 0, max(0, $limit - $rows));
                        $rows += count($chunk);
                        if ($rows >= $limit && count($chunk) == 0) {
                            break;
                        }
                    } else {
                        $rows += count($chunk);
                    }

                    ImportCsvJob::dispatch($chunk, $file->id);
                    $chunks++;

                    if ($limit !== null && $rows >= $limit) {
                        break;
                    }
                }

                // Sheet tidak ketemu / kosong: jangan ditandai selesai supaya
                // ketahuan dan bisa dijalankan ulang setelah berkasnya diperbaiki.
                if ($chunks == 0) {
                    $this->warn("Tidak ada baris terbaca dari {$file->file_name_original} (sheet {$sheet_name} kosong?)");
                    continue;
                }

                // Impor terbatas hanya untuk uji coba: berkasnya sengaja tidak
                // ditandai selesai supaya sisa barisnya tetap ikut pada impor penuh.
                // Konsekuensinya baris yang sudah masuk akan terimpor dua kali.
                if ($limit !== null) {
                    $this->warn("Impor terbatas: {$rows} baris pertama {$file->file_name_original} (sheet {$sheet_name}), berkas belum ditandai selesai.");
                    continue;
                }

                $this->file_repo->updateRecord($file->id, ['is_sync' => true]);
                $this->info("Queue import was created for {$file->file_name_original} ({$chunks} bungkus)...");
                continue;
            }

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
