<?php

namespace App\Repositories;

use App\Repositories\Master\BudgetSourceRepository;
use App\Repositories\Master\BudgetYearRepository;
use App\Repositories\Master\RegionRepository;
use App\Repositories\Master\UserAccessRepository;
use App\Repositories\Master\UserRepository;
use App\Repositories\System\FileRepository;
use App\Repositories\System\OptionRepository;
use App\Repositories\Master\DashboardRepository;
use App\Repositories\Master\OrganizationRepository;
use App\Repositories\Master\ProgramTemplateRepository;
use App\Repositories\Transaction\ProgramRepository;
use Carbon\Carbon;

class CompileRepository
{
    protected $user_access_repo;
    protected $user_repo;
    protected $file_repo;
    protected $option_repo;
    protected $region_repo;
    protected $dashboard_repo;
    protected $budget_year_repo;
    protected $organization_repo;
    protected $budget_source_repo;
    protected $program_repo;
    protected $program_template_repo;

    public function __construct(
        UserAccessRepository $user_access_repo,
        UserRepository $user_repo,
        FileRepository $file_repo,
        OptionRepository $option_repo,
        RegionRepository $region_repo,
        DashboardRepository $dashboard_repo,
        BudgetYearRepository $budget_year_repo,
        OrganizationRepository $organization_repo,
        BudgetSourceRepository $budget_source_repo,
        ProgramRepository $program_repo,
        ProgramTemplateRepository $program_template_repo
    )
    {
        $this->user_access_repo = $user_access_repo;
        $this->user_repo = $user_repo;
        $this->file_repo = $file_repo;
        $this->option_repo = $option_repo;
        $this->region_repo = $region_repo;
        $this->dashboard_repo = $dashboard_repo;
        $this->budget_year_repo = $budget_year_repo;
        $this->organization_repo = $organization_repo;
        $this->budget_source_repo = $budget_source_repo;
        $this->program_repo = $program_repo;
        $this->program_template_repo = $program_template_repo;
    }

    public function make(&$data)
    {
        // generate fields
        if(count($data['fields']) > 0){
            foreach ($data['fields'] as $key => $value) {
                switch ($value['type']) {
                    case 'data':
                    case 'data-multi':
                        $table = $value['data_table'] ?? '';
                        $condition = $value['data_condition'] ?? '';
                        $extra = $value['data_extra'] ?? '';
                        $getOptions = $this->getOptions($table, $condition, $extra);
                        $data['fields'][$key]['options'] = [];
                        if(count($getOptions) > 0){
                            $data['fields'][$key]['options'] = $getOptions;
                        }
                        break;
                    case 'datetime':
                        $data['fields'][$key]['value'] = Carbon::now()->format('Y-m-d H:i');
                        break;
                    case 'date':
                        $data['fields'][$key]['value'] = Carbon::now()->format('Y-m-d');
                        break;
                    case 'tabs':
                        foreach ($value['details'] as $tab_name => $tab_value) {
                            foreach ($tab_value['columns'] as $tab_column_index => $tab_column) {
                                switch ($tab_column['type']) {
                                    case 'data':
                                        $table = $tab_column['table'] ?? '';
                                        $condition = $tab_column['condition'] ?? '';
                                        $extra = $value['extra'] ?? '';
                                        $getOptions = $this->getOptions($table, $condition, $extra);
                                        $data['fields'][$key]['details'][$tab_name]['columns'][$tab_column_index]['options'] = [];
                                        if(count($getOptions) > 0){
                                            $data['fields'][$key]['details'][$tab_name]['columns'][$tab_column_index]['options'] = $getOptions;
                                        }
                                        break;

                                    default:
                                        # code...
                                        break;
                                }
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        // pass data if not empty
        if(count($data['datas']) > 0){
            foreach ($data['fields'] as $key => $value) {
                switch ($value['type']) {
                    case 'data':
                    case 'data-multi':
                    case 'text':
                    case 'hidden':
                    case 'text-area':
                    case 'ckeditor':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? '';
                        break;
                    case 'datetime':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? Carbon::now()->format('Y-m-d H:i');
                        break;
                    case 'date':
                        $data['fields'][$key]['value'] = $data['datas'][$key] == null ? Carbon::now()->format('Y-m-d') : Carbon::parse($data['datas'][$key])->format('Y-m-d');
                        break;
                    case 'checkbox':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? '0';
                        break;
                    case 'image':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? '0';
                        break;
                    case 'map-marker':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? 'null';
                    case 'number':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? 0;
                        break;
                    case 'decimal':
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? 0.00;
                        break;
                    case 'tabs':
                        break;
                    default:
                        $data['fields'][$key]['value'] = $data['datas'][$key] ?? '';
                        break;
                }
            }
        }
        return $data;
    }

    public function getOptions($table,$condition,$extra = '')
    {
        $results = [];
        $condition = "1=1" . $condition;
        switch ($table) {
            case 'mt_dashboard':
                $records = $this->dashboard_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->title ." ({$record->type}) - Sumber: {$record->source}";
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_user_access':
                $records = $this->user_access_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->user_access_name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_budget_year':
                $records = $this->budget_year_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_user':
                $records = $this->user_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->id . '-' . $record->name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_region':
                $records = $this->region_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->code . '-' . $record->name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_organization':
                $records = $this->organization_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->code . '-' . $record->name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_budget_source':
                $records = $this->budget_source_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->id . '-' . $record->name;
                        array_push($results,$result);
                    }
                }
                break;

            case 'mt_program_template':
                $records = $this->program_template_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->code . '-' . $record->concern;
                        array_push($results,$result);
                    }
                }
                break;

            case 'sy_file':
                $records = $this->file_repo->getOptionRecords($condition);
                if($records->count() > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record->id;
                        $result['label'] = $record->id . '-' . $record->file_name_original;
                        array_push($results,$result);
                    }
                }
                break;

            case 'sy_option':
                $records = $this->option_repo->getOptionRecords($condition);
                if(count($records) > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record['id'];
                        $result['label'] = $record['value'];
                        array_push($results,$result);
                    }
                }
                break;

            case 'tr_program':
                $records = $this->program_repo->getOptionRecords($condition);
                if(count($records) > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record['id'];
                        $result['label'] = $record['budget_year_name'] .' | '. $record['district_name'] .' | '. $record['subdistrict_name'] .' | '. $record['code'] .' | '. $record['sub_activity'].' | '. $record['organization_name'];
                        ($record['created_by_organization_name'] != $record['organization_name'] ? $result['label'] .= ' | '. $record['created_by_organization_name'] : '');
                        array_push($results,$result);
                    }
                }
                break;

            case 'custom_options':
                $records = $this->option_repo->getCustomOptionRecords($condition);
                if(count($records) > 0){
                    foreach ($records as $record) {
                        $result['id'] = $record['id'];
                        $result['label'] = $record['label'];
                        array_push($results,$result);
                    }
                }
                break;

            default:
                # code...
                break;
        }

        return $results;
    }

    public function convertValueToAlphabet($value)
    {
        $value = (int) $value;

        if($value <= 166){
            $result = 'C';
        } elseif($value <= 332){
            $result = 'B';
        } else {
            $result = 'A';
        }

        return $result;
    }

    public function convertDate($date, $format = "Y-m-d")
    {
        $time = strtotime($date);
        $result = Carbon::parse($time)->format($format);
        return $result;
    }

    public function convertValueToFloat($value = '0')
    {
        return floatval(str_replace(',', '', $value));
    }

    public function convertDetails($table_name, $data_array)
    {
        $details = [];
        $details[$table_name . '_insert'] = [];
        $details[$table_name . '_update'] = [];
        $details[$table_name . '_delete'] = [];

        if(is_array($data_array)){
            $data_details = [];
            foreach ($data_array as $column_name => $values) {
                foreach ($values as $index => $value) {
                    $data_details[$index][$column_name] = $value;
                }
            }

            foreach ($data_details as $key => $detail) {
                if ($detail['id'] == '0-deleted') {
                    unset($data_details[$key]);
                }
            }

            foreach ($data_details as $data_detail) {
                if($data_detail['id'] == '0') {
                    unset($data_detail['id']);
                    array_push($details[$table_name . '_insert'], $data_detail);
                } else {
                    if (strstr($data_detail['id'], '-deleted')){
                        $data_detail['id'] = str_replace('-deleted','',$data_detail['id']);
                        array_push($details[$table_name . '_delete'], $data_detail);
                    } else {
                        array_push($details[$table_name . '_update'], $data_detail);
                    }
                }
            }
        }
        return $details;
    }

    public function makeField($module_details, &$columns)
    {
        if ($module_details->count() > 0) {
            foreach ($module_details as $module_detail) {
                switch ($module_detail['type']) {
                    case 'TEXT':
                        $columns[$module_detail['column_name']] = [
                            'label' => "{$module_detail['column_mark']}",
                            'name' => $module_detail['column_name'],
                            'placeholder' => "{$module_detail['column_mark']}",
                            'type' => 'text',
                            'maxlength' => 100,
                            'required' => true,
                            'show_only' => false,
                            'validate_message' => "{$module_detail['column_mark']} wajib diisi",
                            'show_list_flag' => (bool) $module_detail['show_list_flag'],
                            'module_detail_id' => $module_detail['id'],
                            'is_multilang' => (bool) $module_detail['multilang_flag']
                        ];
                        break;
                    case 'CHECKBOX':
                        $columns[$module_detail['column_name']] = [
                            'label' => "{$module_detail['column_mark']}",
                            'name' => $module_detail['column_name'],
                            'placeholder' => "{$module_detail['column_mark']}",
                            'type' => 'checkbox',
                            'required' => false,
                            'show_only' => false,
                            'validate_message' => "{$module_detail['column_mark']} wajib diisi",
                            'show_list_flag' => (bool) $module_detail['show_list_flag'],
                            'module_detail_id' => $module_detail['id'],
                            'is_multilang' => (bool) $module_detail['multilang_flag']
                        ];
                        break;
                    case 'CKEDITOR':
                        $columns[$module_detail['column_name']] = [
                            'label' => "{$module_detail['column_mark']}",
                            'name' => $module_detail['column_name'],
                            'placeholder' => "",
                            'type' => 'ckeditor',
                            'required' => false,
                            'show_only' => false,
                            'validate_message' => "{$module_detail['column_mark']} wajib diisi",
                            'show_list_flag' => (bool) $module_detail['show_list_flag'],
                            'module_detail_id' => $module_detail['id'],
                            'is_multilang' => (bool) $module_detail['multilang_flag']
                        ];
                        break;
                    case 'TEXT-AREA':
                        $columns[$module_detail['column_name']] = [
                            'label' => "{$module_detail['column_mark']}",
                            'name' => $module_detail['column_name'],
                            'placeholder' => "",
                            'type' => 'text-area',
                            'required' => false,
                            'show_only' => false,
                            'validate_message' => "{$module_detail['column_mark']} wajib diisi",
                            'show_list_flag' => (bool) $module_detail['show_list_flag'],
                            'module_detail_id' => $module_detail['id'],
                            'is_multilang' => (bool) $module_detail['multilang_flag']
                        ];
                        break;
                    case 'IMAGE':
                        $columns[$module_detail['column_name']] = [
                            'label' => "{$module_detail['column_mark']}",
                            'name' => $module_detail['column_name'],
                            'placeholder' => "",
                            'type' => 'image',
                            'image_width' => ($module_detail['image_width'] == 0 ? 600 : $module_detail['image_width']),
                            'image_height' => ($module_detail['image_height'] == 0 ? 400 : $module_detail['image_height']),
                            'value' => 0,
                            'required' => false,
                            'show_only' => false,
                            'validate_message' => "{$module_detail['column_mark']} wajib diisi",
                            'show_list_flag' => (bool) $module_detail['show_list_flag'],
                            'module_detail_id' => $module_detail['id'],
                            'is_multilang' => (bool) $module_detail['multilang_flag']
                        ];
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }
    }

    public function generateDigit($digit, $number)
    {
        $result = '';
        for ($i = 0; $i < $digit - (strlen($number)); $i++) {
            $result .= '0';
        }
        $result .= $number;
        return $result;
    }

    public function validateRule($fields, $except = [])
    {
        $rules = [];
        foreach ($fields as $key => $field) {
            if (in_array($key, $except)) {
                continue;
            }
            $val = [];
            switch ($field['type']) {
                case 'text':
                case 'text-area':
                    if (isset($field['required']) && $field['required']) {
                        array_push($val, 'required');
                    }
                    if (isset($field['maxlength']) && $field['maxlength']) {
                        array_push($val, 'max:'. (int) $field['maxlength']);
                    }
                    break;

                case 'data':
                    if (isset($field['required']) && $field['required']) {
                        array_push($val, 'required');
                    }
                    break;

                default:
                    //
                    break;
            }
            if (count($val) > 0) {
                $rules[$key] = implode("|",$val);
            }
        }

        return $rules;
    }

    public function customDivide($value1, $value2)
    {
        if ($value2 == 0) {
            return 0;
        }
        return $value1 / $value2;
    }
}
