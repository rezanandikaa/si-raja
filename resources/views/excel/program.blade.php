<table>
    <thead>
        {{-- Baris pertama: kolom biasa rowspan="2", "Aktivitas Real" menaungi 3 kolom di bawahnya. --}}
        <tr>
            <th rowspan="2">Nomor</th>
            <th rowspan="2">Strategi OPPKPKE</th>
            <th rowspan="2">Perangkat Daerah</th>
            <th rowspan="2">Kode</th>
            <th rowspan="2">Program</th>
            <th rowspan="2">Kegiatan</th>
            <th rowspan="2">Sub Kegiatan</th>
            <th colspan="3">Aktivitas Real</th>
            <th rowspan="2">Alokasi Anggaran (Rp)</th>
            <th rowspan="2">Sumber Pembiayaan</th>
            <th rowspan="2">Sifat Bantuan</th>
            <th rowspan="2">Lokasi</th>
            <th rowspan="2">Jumlah Sasaran Penerima Manfaat</th>
            <th rowspan="2">Besaran Manfaat</th>
            <th rowspan="2">Jenis Bantuan</th>
            <th rowspan="2">Durasi Penerima Bantuan</th>
            <th colspan="4">Realisasi {{ $budget_year_name }}</th>
            <th rowspan="2">Total Realisasi</th>
        </tr>
        <tr>
            <th>Langsung</th>
            <th>Tidak Langsung</th>
            <th>Penunjang</th>
            <th>Triwulan I</th>
            <th>Triwulan II</th>
            <th>Triwulan III</th>
            <th>Triwulan IV</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        {{-- Satu tabel, satu header. Tiap Strategi OPPKPE dibuka baris pemisah
             (colspan 23 = seluruh kolom A..W). --}}
        @foreach($groups as $strategi => $records)
            <tr>
                <td colspan="23">{{ $strategi }}</td>
            </tr>
            @foreach($records as $record)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $record['goal_value'] }}</td>
                <td>{{ $record['organization_name'] }}</td>
                <td>{{ $record['code'] }}</td>
                <td>{{ $record['program'] }}</td>
                <td>{{ $record['activity'] }}</td>
                <td>{{ $record['sub_activity'] }}</td>
                <td>{{ $record['aktivitas_real_langsung'] }}</td>
                <td>{{ $record['aktivitas_real_tidak_langsung'] }}</td>
                <td>{{ $record['aktivitas_real_penunjang'] }}</td>
                <td>{{ $record['budget_allocation'] }}</td>
                <td>{{ $record['budget_source'] }}</td>
                <td>{{ $record['sifat_bantuan'] }}</td>
                {{-- nl2br: HTML reader PhpSpreadsheet mengubah <br> jadi newline di dalam sel. --}}
                <td>{!! nl2br(e($record['lokasi'])) !!}</td>
                <td>{!! nl2br(e($record['sasaran_penerima_manfaat'])) !!}</td>
                <td>{!! nl2br(e($record['besaran_manfaat'])) !!}</td>
                <td>{!! nl2br(e($record['jenis_bantuan'])) !!}</td>
                <td>{!! nl2br(e($record['durasi_penerima_bantuan'])) !!}</td>
                <td>{{ $record['realization_q1'] }}</td>
                <td>{{ $record['realization_q2'] }}</td>
                <td>{{ $record['realization_q3'] }}</td>
                <td>{{ $record['realization_q4'] }}</td>
                <td>{{ $record['realization_total'] }}</td>
            </tr>
            @php
                $i++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>
