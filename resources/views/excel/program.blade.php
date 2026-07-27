<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tujuan Program</th>
            <th>Perangkat Daerah Pelaksana</th>
            <th>Kode</th>
            <th>Program</th>
            <th>Sub kegiatan</th>
            <th>Alokasi Anggaran (Rp)</th>
            <th>Sumber Pembiayaan</th>
            <th>Realisasi Anggaran (Rp)</th>
            <th>Lokasi (Kecamatan/ Kelurahan/Desa)</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
            <td>4</td>
            <td>5</td>
            <td>6</td>
            <td>7</td>
            <td>8</td>
            <td>9</td>
            <td>10</td>
            <td>11</td>
        </tr>
        <tr>
            <td>No Urut</td>
            <td>"Diisi menurut tiga strategi PPKE yaitu:<br />
                1. Mengurangi Beban Pengeluaran<br />
                2. Meningkatkan Pendapatan<br />
                3. Meminimalkan Wilayah Kantong Kemiskinan"</td>
            <td>Diisi nama unit pelaksana kegiatan atau Perangkat Daerah penanggung jawab (contoh: Bidang Kesmas Dinas Kesehatan)</td>
            <td>Diisi dengan kode subkegiatan nomenklatur Permendagri No 050-5889 Tahun 2021. (contoh: 1 06 05 2.02 0001 )</td>
            <td>Diisi dengan nama program terkait penanggulangan kemiskinan ekstrem sesuai Permendagri No 050-5889 Tahun 2021</td>
            <td>Diisi dengan nama sub kegiatan terkait penanggulangan kemiskinan ekstrem sesuai Permendagri No 050-5889 Tahun 2021</td>
            <td>Diisi dengan jumlah/besaran alokasi anggaran dalam rupiah untuk program dan kegiatan</td>
            <td>Diisi dengan sumber pembiayaan untuk program, kegiatan/sub kegiatan, antara lain: APBN, APBD Provinsi, APBD Kab/Kota, APBDes dan sumber lain (Lembaga Donor, LSM, Dunia Usaha, Masyarakat, dsb)</td>
            <td>Diisi dengan realisasi anggaran dalam rupiah untuk intervensi program dan kegiatan</td>
            <td>Diisi dengan nama desa lokasi kegiatan dalam implementasi program</td>
            <td>Diisi dengan catatan</td>
        </tr>
        @php
            $i = 1;
        @endphp
        @foreach($data as $record)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $record['goal_value'] }}</td>
                <td>{{ $record['organization_name'] }}</td>
                <td>{{ $record['code'] }}</td>
                <td>{{ $record['program'] }}</td>
                <td>{{ $record['sub_activity'] }}</td>
                <td>{{ $record['budget_allocation'] }}</td>
                <td>{{ $record['budget_source'] }}</td>
                <td>{{ $record['budget_realization'] }}</td>
                <td>Desa/Kelurahan {{ $record['subdistrict_name'] }}, Kec. {{ $record['district_name'] }}</td>
                <td>{{ $record['program_description'] }}</td>
            </tr>
            @php
                $i++;
            @endphp
        @endforeach
    </tbody>
</table>
