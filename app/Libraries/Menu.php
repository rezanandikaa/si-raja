<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class Menu
{
    public static function getAll()
    {
        $user = Auth::user();
        $access_menus = [];
        $access_modules = DB::table('mt_user_access')->find($user->user_access_id);
        if($access_modules != null){
            $access_menus = json_decode($access_modules->access_module, true);
        }

        $menus = self::listMenu();
        for ($i=0; $i < count($menus); $i++) {
            foreach ($access_menus as $access_menu) {
                if($menus[$i]['has_child']){
                    $j=0;
                    foreach ($menus[$i]['childs'] as $child) {
                        if($access_menu['module'] == $child['id']){
                            $menus[$i]['childs'][$j]['active_flag'] = $access_menu['active_flag'];
                        }
                        $j++;
                    }
                } else {
                    if($access_menu['module'] == $menus[$i]['id']){
                        $menus[$i]['active_flag'] = $access_menu['active_flag'];
                    }
                }
            }
        }
        $html = "";
        foreach ($menus as $menu) {
            if(!$menu['has_child']){
                $route_name = $menu['action'] ?? 'home';
                if($menu['active_flag']){
                    $html .= "
                        <li class='". (Route::currentRouteName() == $route_name ? 'active' : '')."'>
                            <a href='".route($route_name, (isset($menu['action_prop']) ? $menu['action_prop'] : []))."'>
                                <i class='{$menu['class']}'><span class='path1'></span><span class='path2'></span></i>
                                <span>{$menu['title']}</span>
                            </a>
                        </li>";
                }
            } else {
                $total_child = count($menu['childs']);
                foreach ($menu['childs'] as $child) {
                    if(!$child['active_flag']){
                        $total_child -= 1;
                    }
                }
                $show_parent_menu = true;
                if($total_child == 0){
                    $show_parent_menu = false;
                }
                if($show_parent_menu) {
                    $route_names = [];
                    foreach ($menu['childs'] as $child) {
                        $act = $child['action'] ?? '';
                        if ($act != ''){
                            array_push($route_names, $act);
                        }
                    }
                    $html .= "
                        <li class='".(in_array(Route::currentRouteName(), $route_names) ? 'active' : '')."'>
                            <a href='#{$menu['id']}' class='has-arrow'>
                                <i class='{$menu['class']}'></i>
                                <span>{$menu['title']}</span>
                            </a>
                            <ul>";
                            foreach ($menu['childs'] as $child) {
                                $route_name = $child['action'] ?? 'home';
                                if($child['active_flag']){
                                    $html .= "<li><a href='".route($route_name, (isset($child['action_prop']) ? $child['action_prop'] : []))."'>{$child['sub_title']}</a></li>";
                                }
                            }
                    $html .= "
                            </ul>
                        </li>
                    ";
                }
            }
        }
        return $html;
    }

    public static function listMenu()
    {
        $menus = [];

        $menu = [
            'title' => 'Grafik',
            'id' => 't_graph',
            'class' => 'fa fa-pie-chart',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Data Grafik', 'id' => 'tr_dashboard', 'action' => 'dashboard.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Galeri',
            'id' => 't_gallery',
            'class' => 'fa-solid fa-images',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Data Galeri', 'id' => 'tr_gallery', 'action' => 'gallery.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Kegiatan',
            'id' => 't_program',
            'class' => 'fa-solid fa-seedling',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Rencana Kegiatan', 'id' => 'tr_program', 'action' => 'program.list', 'active_flag' => false],
                ['sub_title' => 'Realisasi Kegiatan', 'id' => 'tr_program_realization', 'action' => 'program.realization.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Unduhan',
            'id' => 't_download',
            'class' => 'fa fa-download',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Laporan Program', 'id' => 'tr_download', 'action' => 'download.report', 'action_prop' => ['report_name' => 'program'], 'active_flag' => false],
                ['sub_title' => 'Laporan Realisasi', 'id' => 'tr_download', 'action' => 'download.report', 'action_prop' => ['report_name' => 'realization'], 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Data Master',
            'id' => 't_master',
            'class' => 'fa fa-database',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'P3KE Individu', 'id' => 'mt_destitution_nik', 'action' => 'master.destitution_nik.list', 'active_flag' => false],
                ['sub_title' => 'P3KE Kepala Keluarga', 'id' => 'mt_destitution_kk', 'action' => 'master.destitution_kk.list', 'active_flag' => false],
                ['sub_title' => 'Templat Program', 'id' => 'mt_program_template', 'action' => 'master.program_template.list', 'active_flag' => false],
                ['sub_title' => 'Templat Grafik', 'id' => 'mt_dashboard', 'action' => 'master.dashboard.list', 'active_flag' => false],
                ['sub_title' => 'Sumber Pembiayaan', 'id' => 'mt_budget_source', 'action' => 'master.budget_source.list', 'active_flag' => false],
                ['sub_title' => 'Perangkat Daerah', 'id' => 'mt_organization', 'action' => 'master.organization.list', 'active_flag' => false],
                ['sub_title' => 'Tahun Anggaran', 'id' => 'mt_budget_year', 'action' => 'master.budget_year.list', 'active_flag' => false],
                ['sub_title' => 'Hak Akses', 'id' => 'mt_user_access', 'action' => 'master.user_access.list', 'active_flag' => false],
                ['sub_title' => 'Pengguna', 'id' => 'mt_user', 'action' => 'master.user.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Alat',
            'id' => 't_tool',
            'class' => 'fa fa-wrench',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Cek Program', 'id' => 'to_program', 'action' => 'tool.program.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        $menu = [
            'title' => 'Sistem',
            'id' => 't_system',
            'class' => 'fa fa-cogs',
            'has_child' => true,
            'childs' => [
                ['sub_title' => 'Preferensi', 'id' => 'sy_preference', 'action' => 'system.preference.list', 'active_flag' => false],
                ['sub_title' => 'Berkas', 'id' => 'sy_file', 'action' => 'system.file.list', 'active_flag' => false],
                ['sub_title' => 'Pilihan', 'id' => 'sy_option', 'action' => 'system.option.list', 'active_flag' => false],
                ['sub_title' => 'Log Impor', 'id' => 'sy_import', 'action' => 'system.import.list', 'active_flag' => false],
                ['sub_title' => 'Sumber Data', 'id' => 'sy_data', 'action' => 'system.data.list', 'active_flag' => false],
                ['sub_title' => 'Log Aktivitas', 'id' => 'sy_log_activity', 'action' => 'system.log_activity.list', 'active_flag' => false],
            ]
        ];
        array_push($menus, $menu);

        return $menus;
    }
}
