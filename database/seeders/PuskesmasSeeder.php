<?php

namespace Database\Seeders;

use App\Models\Master\Mt_organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PuskesmasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parent = Mt_organization::where('code', 'DINKES')->first();
        if ($parent) {
            $pkm_datas = [
                [
                    'name' => strtoupper('Puskesmas Banjarsari'),
                    'email' => 'pkm.banjarsari@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Bojongjuruh'),
                    'email' => 'pkm.bojongjuruh@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Bayah'),
                    'email' => 'pkm.bayah@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Bojongmanik'),
                    'email' => 'pkm.bojongmanik@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cibadak'),
                    'email' => 'pkm.cibadak@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Mandala'),
                    'email' => 'pkm.mandala@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cibeber'),
                    'email' => 'pkm.cibeber@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cisungsang'),
                    'email' => 'pkm.cisungsang@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Citorek'),
                    'email' => 'pkm.citorek@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cigemblong'),
                    'email' => 'pkm.cigemblong@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cihara'),
                    'email' => 'pkm.cihara@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cijaku'),
                    'email' => 'pkm.cijaku@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cikulur'),
                    'email' => 'pkm.cikulur@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Pamandegan'),
                    'email' => 'pkm.pamandegan@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cileles'),
                    'email' => 'pkm.cileles@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Prabugantungan'),
                    'email' => 'pkm.prabugantungan@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cilograng'),
                    'email' => 'pkm.cilograng@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cimarga'),
                    'email' => 'pkm.cimarga@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Sarageni'),
                    'email' => 'pkm.sarageni@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cipanas'),
                    'email' => 'pkm.cipanas@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cirinten'),
                    'email' => 'pkm.cirinten@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Curugbitung'),
                    'email' => 'pkm.curugbitung@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Gunungkencana'),
                    'email' => 'pkm.gunungkencana@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Gunungkendeng'),
                    'email' => 'pkm.gunungkendeng@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Kalanganyar'),
                    'email' => 'pkm.kalanganyar@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Lebakgedong'),
                    'email' => 'pkm.lebakgedong@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Leuwidamar'),
                    'email' => 'pkm.leuwidamar@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cisimeut'),
                    'email' => 'pkm.cisimeut@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Maja'),
                    'email' => 'pkm.maja@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Malingping'),
                    'email' => 'pkm.malingping@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Cipendeuy'),
                    'email' => 'pkm.cipendeuy@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Muncang'),
                    'email' => 'pkm.muncang@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Panggarangan'),
                    'email' => 'pkm.panggarangan@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Rangkasbitung'),
                    'email' => 'pkm.rangkasbitung@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Mekarsari'),
                    'email' => 'pkm.mekarsari@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Kolelet'),
                    'email' => 'pkm.kolelet@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Sajira'),
                    'email' => 'pkm.sajira@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Pajagan'),
                    'email' => 'pkm.pajagan@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Sobang'),
                    'email' => 'pkm.sobang@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Binuangeun'),
                    'email' => 'pkm.binuangeun@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Parungsari'),
                    'email' => 'pkm.parungsari@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Warunggunung'),
                    'email' => 'pkm.warunggunung@lebakkab.go.id',
                ],
                [
                    'name' => strtoupper('Puskesmas Baros'),
                    'email' => 'pkm.baros@lebakkab.go.id',
                ],
            ];

            DB::beginTransaction();
            try {
                foreach ($pkm_datas as $pkm_data) {
                    $record = Mt_organization::create([
                        'code' => str_replace(' ', '-', $pkm_data['name']),
                        'name' => $pkm_data['name'],
                        'parent_id' => $parent->id,
                        'created_by_id' => 1,
                        'updated_by_id' => 1
                    ]);
                    $user_access_id = 2;
                    User::create([
                        'name' => $pkm_data['name'],
                        'email' => $pkm_data['email'],
                        'gender' => 'L',
                        'active_flag' => true,
                        'user_access_id' => $user_access_id,
                        'organization_id' => $record->id,
                        'budget_year_id' => get_preference('default_budget_year', 0),
                        'password' => bcrypt('12345678'),
                        'created_by_id' => 1,
                        'updated_by_id' => 1
                    ]);
                }
                DB::commit();
            } catch (\Exception $err) {
                DB::rollBack();
                Log::error($err->getMessage());
            }
        }
    }
}
