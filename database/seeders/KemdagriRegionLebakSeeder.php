<?php

namespace Database\Seeders;

use App\Models\Master\Mt_region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KemdagriRegionLebakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            //code...
            $data = [
                'parent_id' => 0,
                'code' => '36',
                'name' => 'BANTEN',
                'type' => '1-PROVINSI'
            ];
            $province = Mt_region::create($data);

            $data = [
                'parent_id' => $province->id,
                'code' => $province->code.'02',
                'name' => 'LEBAK',
                'type' => '2-KABUPATEN/KOTA'
            ];
            $regency = Mt_region::create($data);

            // MALINGPING
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'01',
                'name' => 'MALINGPING',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILANGKAHAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'PAGELARAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKARAJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'MALINGPING UTARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOLANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPEUNDEUY'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'RAHONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUJAJAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'KERSARATU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'MALINGPING SELATAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUMBERWARAS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2023', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMANAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2024', 'type' => '4-DESA-KELURAHAN', 'name' => 'SENANGHATI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2026', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGIANG'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // PANGGARANGAN
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'02',
                'name' => 'PANGGARANGAN',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'PANGGARANGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'JATAKE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'SOGONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMANDIRI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGGEDE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SITUREGEN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGRATU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'HEGARMANAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAJADI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBARENGKOK'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // BAYAH
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'03',
                'name' => 'BAYAH',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'BAYAH BARAT'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'SAWARNA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIDIKIT'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUWAKAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMANCAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'DARMASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'BAYAH TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISUREN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRGOMBONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'SAWARNA TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'PAMUBULAN'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIPANAS
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'04',
                'name' => 'CIPANAS',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPANAS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'GIRILAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'MALANGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'BINTANGRESMI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'TALAGAHIYANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'LUHURJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'HAURGAJRUG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'GIRIHARJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'JAYAPURA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'SIPAYUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'BINTANGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRHAUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2020', 'type' => '4-DESA-KELURAHAN', 'name' => 'HARUMSARI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // MUNCANG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'05',
                'name' => 'MUNCANG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIREURIH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMINYAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUNCANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEUWICOO'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKARANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKANAGARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRNANGKA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'JAGARAKSA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'TANJUNGWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2020', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2021', 'type' => '4-DESA-KELURAHAN', 'name' => 'GIRIJAGABAYA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // LEUWIDAMAR
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'06',
                'name' => 'LEUWIDAMAR',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'KANEKES'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISIMEUT'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBUNGUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEUWIDAMAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKPARAHIANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGKANWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'NAYAGATI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONGMENTENG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGAWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'WANTISARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'JALUPANGMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISIMEUT RAYA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // BOJONGMANIK
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'07',
                'name' => 'BOJONGMANIK',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONGMANIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'HARJAWANA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADURAHAYU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMAYANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARAKANBEUSI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'KEBONCAU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARMANIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKAR RAHAYU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRBITUNG'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // GUNUNGKENCANA
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'08',
                'name' => 'GUNUNGKENCANA',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGKENCANA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMANYANGRAY'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGKENDENG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISAMPANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIGINGGANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIAKAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CICARINGIN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'BULAKAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKANEGARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONGKONENG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'KRAMATJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'TANJUNGSARI INDAH'],

            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // BANJARSARI
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'09',
                'name' => 'BANJARSARI',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'KERTA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONGJURUH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEUWIIPUH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKKEUSIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILEGONGILIR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'KEUSIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBATURKEUSIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'KUMPAY'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'JALUPANGGIRANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'BENDUNGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'KERTARAHARJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISAMPIH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'TAMANSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIDAHU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIRUJI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUHAUK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'LABANJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2020', 'type' => '4-DESA-KELURAHAN', 'name' => 'UMBULJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2021', 'type' => '4-DESA-KELURAHAN', 'name' => 'KERTARAHAYU'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CILELES
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'10',
                'name' => 'CILELES',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASINDANGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARUNGKUJANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILELES'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKAREO'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPADANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUMURUH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'PRABUGANTUNGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'DAROYON'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGAMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'KUJANGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'BANJARSARI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIMARGA
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'11',
                'name' => 'CIMARGA',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'SARAGENI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGANTEN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUDAMANIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'TAMBAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMARGA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'KARYAJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGAJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'JAYAMANIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGALUYU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGATIRTA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'INTENJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'JAYASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'GIRIMUKTI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGKANMANIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGIANGJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARMULYA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // SAJIRA
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'12',
                'name' => 'SAJIRA',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'SAJIRA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKARAME'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CALUNGBUNGUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARUNGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMARGA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'PAJAGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'SAJIRA MEKAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'PAJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'MARGALUYU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'BUNGURMEKAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIUYAH'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // MAJA
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'13',
                'name' => 'MAJA',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'TANJUNGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'MAJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGIANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'BINONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUBUGAN CIBEUREUM'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'PADASUKA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'CURUGBADAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILANGKAP'],
                ['parent_id' => $district->id, 'code' => $district->code.'2020', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRKEMBANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2021', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRKECAPI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2022', 'type' => '4-DESA-KELURAHAN', 'name' => 'BUYUT MEKAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2023', 'type' => '4-DESA-KELURAHAN', 'name' => 'MAJA BARU'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // RANGKASBITUNG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'14',
                'name' => 'RANGKASBITUNG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'1002', 'type' => '4-DESA-KELURAHAN', 'name' => 'RANGKASBITUNG BARAT'],
                ['parent_id' => $district->id, 'code' => $district->code.'1006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIJORO LEBAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'1007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUARA CIUJUNG BARAT'],
                ['parent_id' => $district->id, 'code' => $district->code.'1008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIJORO PASIR'],
                ['parent_id' => $district->id, 'code' => $district->code.'1012', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUARA CIUJUNG TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRTANJUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITERAS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'NAMENG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'KOLELET WETAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'JATIMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'PABUARAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'RANGKASBITUNG TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMANAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2021', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMANGEUNTEUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2023', 'type' => '4-DESA-KELURAHAN', 'name' => 'NARIMBANG MULIA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // WARUNGGUNUNG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'15',
                'name' => 'WARUNGGUNUNG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKARENDAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'WARUNGGUNUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBUAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIR TANGKIL'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'BAROS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'BANJARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'PADASUKA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKARAJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'JAGABAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SELARAJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'CEMPAKA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGSARI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIJAKU
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'16',
                'name' => 'CIJAKU',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPALABUH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIJAKU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBEUREUM'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIAPUS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'KANDANGSAPI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIHUJAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMENGA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'KAPUNDUHAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKASENANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKARATUAN'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIKULUR
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'17',
                'name' => 'CIKULUR',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'ANGGALAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUNCANGKOPONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAHARJA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'TAMANJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIGOONG UTARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUARADUA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKULUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CURUGPANJANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIGOONG SELATAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUMURBANDUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARAGE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKADAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRGINTUNG'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIBADAK
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'18',
                'name' => 'CIBADAK',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'ASEM'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'ASEM MARGALUYU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONG LELES'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'BOJONGCAE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBADAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIMENTENG JAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISANGU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUAGUNG BARAT'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUAGUNG TENGAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUAGUNG TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'MALABAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKAR AGUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'PANANCANGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASAR KEONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'TAMBAKBAYA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIBEBER
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'19',
                'name' => 'CIBEBER',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBEBER'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITOREK TENGAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISUNGSANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'KUJANGJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'KUJANGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'NEGLASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKOTOK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITOREK TIMUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'WARUNGBANTEN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'HEGARMANAH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2014', 'type' => '4-DESA-KELURAHAN', 'name' => 'SITUMULYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2015', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITOREK KIDUL'],
                ['parent_id' => $district->id, 'code' => $district->code.'2016', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKADU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2017', 'type' => '4-DESA-KELURAHAN', 'name' => 'SIRNAGALIH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2018', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIHAMBALI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2019', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITOREK BARAT'],
                ['parent_id' => $district->id, 'code' => $district->code.'2020', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGWANGUN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2021', 'type' => '4-DESA-KELURAHAN', 'name' => 'WANASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2022', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITOREK SABRANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2023', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIHERANG'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CILOGRANG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'20',
                'name' => 'CILOGRANG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILOGRANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBARENO'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKAMUNDING'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIJENGKOL'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRBUNGUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKTIPAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKATOMAS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'GIRIMUKTI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIREUNDEU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'GUNUNGBATU'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // WANASALAM
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'21',
                'name' => 'WANASALAM',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'WANASALAM'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'BEJOD'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILANGKAP'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPEUCANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARUNGPANJANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKEUSIK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'KATAPANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CISARAP'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKATANI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2011', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPEDANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2012', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARUNGSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2013', 'type' => '4-DESA-KELURAHAN', 'name' => 'KARANGPAMINDANGAN'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // SOBANG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'22',
                'name' => 'SOBANG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPARASI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'SOBANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINDANGLAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMAJU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'HARIANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MAJASARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'SINAR JAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIROMPANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKARESMI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CURUGBITUNG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'23',
                'name' => 'CURUG BITUNG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'GURADOG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CURUGBITUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CANDI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'MAYAK'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPINING'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILAYANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBURUY'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'SEKARWANGI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIDADAP'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKASIH'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // KALANGANYAR
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'24',
                'name' => 'KALANGANYAR',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'KALANGANYAR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'PASIRKUPA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILANGKAP'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'AWEH'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'SANGIANGTANJUNG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'SUKAMEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKATAPIS'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // LEBAKGEDONG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'25',
                'name' => 'LEBAKGEDONG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'BANJARIRIGASI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CILADAEUN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKGEDONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'BANJARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKSITU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKSANGKA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIHARA
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'26',
                'name' => 'CIHARA',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'PANYAUNGAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIHARA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIPARAHU'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'PONDOKPANJANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CITEUPUSEUN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'LEBAKPEUNDEUY'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'MEKARSARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'KARANGKAMULYAN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'BARUNAI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIRINTEN
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'27',
                'name' => 'CIRINTEN',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'DATARCAE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIRINTEN'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'KARANGNUNGGAL'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'KADUDAMAS'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'BADUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'PARAKANLIMA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'NANGGERANG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'CEMPAKA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2009', 'type' => '4-DESA-KELURAHAN', 'name' => 'KAROYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2010', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBARANI'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }

            // CIGEMBLONG
            $data = [
                'parent_id' => $regency->id,
                'code' => $regency->code.'28',
                'name' => 'CIGEMBLONG',
                'type' => '3-KECAMATAN'
            ];
            $district = Mt_region::create($data);

            $subdistricts = [
                ['parent_id' => $district->id, 'code' => $district->code.'2002', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIBUNGUR'],
                ['parent_id' => $district->id, 'code' => $district->code.'2005', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIGEMBLONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2006', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKADONGDONG'],
                ['parent_id' => $district->id, 'code' => $district->code.'2007', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKARET'],
                ['parent_id' => $district->id, 'code' => $district->code.'2004', 'type' => '4-DESA-KELURAHAN', 'name' => 'CIKATE'],
                ['parent_id' => $district->id, 'code' => $district->code.'2003', 'type' => '4-DESA-KELURAHAN', 'name' => 'MUGIJAYA'],
                ['parent_id' => $district->id, 'code' => $district->code.'2001', 'type' => '4-DESA-KELURAHAN', 'name' => 'PEUCANGPARI'],
                ['parent_id' => $district->id, 'code' => $district->code.'2008', 'type' => '4-DESA-KELURAHAN', 'name' => 'WANGUNJAYA'],
            ];
            foreach ($subdistricts as $subdistrict) {
                Mt_region::create($subdistrict);
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::error("Error {$err->getCode()}: {$err->getMessage()}");
        }
    }
}
