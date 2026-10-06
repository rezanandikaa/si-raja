<?php

namespace App\Libraries;

use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Pembaca berkas Excel (.xlsx) berbasis streaming.
 *
 * PhpSpreadsheet (IOFactory::load) memuat seluruh workbook ke memori. Untuk
 * berkas DTSEN 45 MB (~329 MB tak terkompresi, 483 rb baris), membaca header
 * saja tidak selesai dalam 3 menit. OpenSpout membaca per baris: buka
 * workbook ~6 detik (baca sharedStrings), lalu tiap baris ~0,3 ms, memori
 * tetap ~10 MB.
 */
class SheetReader
{
    /** Nama sheet DTSEN Kepala Keluarga. */
    public const SHEET_KK = 'KK';

    /** Nama sheet DTSEN Anggota Rumah Tangga (individu). */
    public const SHEET_ART = 'ART';

    /**
     * Cuplikan isi satu sheet untuk pratinjau di halaman web.
     * Satu kali buka workbook: nama semua sheet + kolom + beberapa baris awal.
     *
     * @return array{sheets: string[], sheet: string|null, columns: string[], rows: array, truncated: bool}
     */
    public function preview($path, $sheet_name = null, $limit = 100)
    {
        $result = [
            'sheets' => [],
            'sheet' => null,
            'columns' => [],
            'rows' => [],
            'truncated' => false,
        ];

        $reader = $this->makeReader();
        $reader->open($path);
        try {
            $first = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                $result['sheets'][] = $sheet->getName();

                $is_target = $sheet_name === null
                    ? $first
                    : strcasecmp($sheet->getName(), $sheet_name) === 0;
                $first = false;

                if (!$is_target) {
                    continue;
                }

                $result['sheet'] = $sheet->getName();
                foreach ($sheet->getRowIterator() as $row) {
                    $values = $this->normalizeRow($row->toArray());
                    if ($result['columns'] === []) {
                        $result['columns'] = array_map(function ($value) {
                            return $value === null ? '' : (string) $value;
                        }, $values);
                        continue;
                    }
                    if (count($result['rows']) >= $limit) {
                        $result['truncated'] = true;
                        break;
                    }
                    $result['rows'][] = $values;
                }
            }
        } finally {
            $reader->close();
        }

        // Nama sheet yang diminta tidak ada di berkas (mis. sheet "KK" dicari
        // tapi berkasnya cuma punya "Sheet1"): pakai sheet pertama supaya
        // halaman tetap menampilkan tabel. Jalur impor memakai aturan sama.
        if ($result['sheet'] === null && $sheet_name !== null) {
            return $this->preview($path, null, $limit);
        }

        return $result;
    }

    /**
     * Stream satu sheet jadi potongan baris asosiatif (baris 1 = nama kolom).
     * Generator: memori tidak tumbuh mengikuti ukuran berkas.
     *
     * @return \Generator
     */
    public function stream($path, $sheet_name = null, $chunk_size = 10000)
    {
        $reader = $this->makeReader();
        $reader->open($path);
        $matched = false;
        try {
            $first = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                $is_target = $sheet_name === null
                    ? $first
                    : strcasecmp($sheet->getName(), $sheet_name) === 0;
                $first = false;

                if (!$is_target) {
                    continue;
                }
                $matched = true;

                $headers = null;
                $chunk = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $values = $this->normalizeRow($row->toArray());

                    // Baris pertama sheet dipakai sebagai nama kolom, mengikuti
                    // bentuk CSV yang sudah dipakai SyncData.
                    if ($headers === null) {
                        $headers = array_map(function ($value) {
                            return $value === null ? '' : (string) $value;
                        }, $values);
                        continue;
                    }

                    $values = array_slice(array_pad($values, count($headers), null), 0, count($headers));
                    if (count(array_filter($values, function ($value) {
                        return $value !== null && $value !== '';
                    })) === 0) {
                        continue;
                    }

                    $chunk[] = array_combine($headers, $values);
                    if (count($chunk) >= $chunk_size) {
                        yield $chunk;
                        $chunk = [];
                    }
                }

                if (count($chunk) > 0) {
                    yield $chunk;
                }
                break;
            }
        } finally {
            $reader->close();
        }

        // Sheet yang diminta tidak ada: baca sheet pertama, sama dengan yang
        // ditampilkan pratinjau di halaman.
        if (!$matched && $sheet_name !== null) {
            yield from $this->stream($path, null, $chunk_size);
        }
    }

    /**
     * Tanpa SHOULD_FORMAT_DATES, sel bertanggal keluar sebagai angka seri
     * Excel (mis. 46053) dan tidak terbaca sebagai tanggal.
     */
    protected function makeReader()
    {
        $options = new Options();
        $options->SHOULD_FORMAT_DATES = true;

        return new Reader($options);
    }

    /**
     * ponytail: tanggal jadi 'Y-m-d', boolean jadi 0/1. Sel angka dibiarkan
     * apa adanya, jadi NIK yang di Excel tersimpan sebagai angka tetap bisa
     * kehilangan nol di depan atau presisi di atas 16 digit. Format kolom itu
     * sebagai Teks di berkas sumber.
     */
    protected function normalizeRow($values)
    {
        return array_map(function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if (is_bool($value)) {
                return (int) $value;
            }
            return $value;
        }, $values);
    }
}
