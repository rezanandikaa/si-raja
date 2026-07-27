<?php

use App\Models\Master\Mt_budget_year;
use App\Models\Master\Mt_organization;
use App\Models\Master\Mt_region;
use App\Models\System\Sy_attachment;
use App\Models\System\Sy_data;
use App\Models\System\Sy_log_activity;
use App\Models\System\Sy_option;
use App\Models\System\Sy_preference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

if (!function_exists('logbook')) {
    function logbook($message, $code = 200)
    {

        $message = "Code {$code}: {$message}";

        $data = [
            'user_id' => Auth::user()->id,
            'activity_date' => Carbon::now(),
            'messages' => $message,
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->header('user-agent')
        ];

        DB::beginTransaction();
        try {
            Sy_log_activity::create($data);
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::error('Error SysLog: ' . $err->getCode());
        }
    }
}

if (!function_exists('is_active')) {
    function is_active($datas, $value)
    {
        $result = '';
        if (in_array($value, $datas)) {
            $result = 'active';
        }
        return $result;
    }
}

if (!function_exists('source_data_active')) {
    function source_data_active()
    {
        $result = 0;
        $record = Sy_data::where('active_flag', true)
            ->whereNull('deleted_at')
            ->first();
        if ($record) {
            $result = $record->id;
        }
        return $result;
    }
}

if (!function_exists('get_option')) {
    function get_option($id, $column = 'value')
    {
        $result = '';
        $record = Sy_option::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        if ($record) {
            $result = $record->$column;
        }
        return $result;
    }
}

if (!function_exists('get_region')) {
    function get_region($id, $column = 'name')
    {
        $result = '';
        $record = Mt_region::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        if ($record) {
            $result = $record->$column;
        }
        return $result;
    }
}

if (!function_exists('get_image')) {
    function get_image($id, $default = null)
    {
        $record = Sy_attachment::find($id);
        return $record ? env('APP_URL') . '/' . $record->path : $default;
    }
}

if (!function_exists('get_preference')) {
    function get_preference($key, $default_value = '')
    {
        $value = $default_value;
        $pref = Sy_preference::where('key', $key)
            ->whereNull('deleted_at')
            ->first();
        if ($pref) {
            $is_images = ['image'];
            if (in_array($pref->key, $is_images)) {
                $value = get_image($pref->value);
            } else {
                $value = $pref->value ?? $default_value;
            }
        }
        return $value;
    }
}

if (!function_exists('is_json')) {
    //Tested thoroughly, Should do the job:
    function is_json(string $json): bool
    {
        json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            return true;
        }
        return false;
    }
}

if (!function_exists('general_organization')) {
    //Tested thoroughly, Should do the job:
    function general_organization($organization_id)
    {
        $orgs = get_preference('general_organization', '[]');
        $orgs = json_decode($orgs, true);
        if (in_array($organization_id, $orgs)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('get_organization')) {
    function get_organization($id, $column = 'name')
    {
        $result = '';
        if ($id != null) {
            $record = Mt_organization::where('id', $id)
                ->whereNull('deleted_at')
                ->first();
            if ($record) {
                $result = $record->$column;
            }
        }
        return $result;
    }
}

if (!function_exists('get_budget_year')) {
    function get_budget_year($id, $column = 'name')
    {
        $result = '';
        if ($id != null) {
            $record = Mt_budget_year::where('id', $id)
                ->whereNull('deleted_at')
                ->first();
            if ($record) {
                $result = $record->$column;
            }
        }
        return $result;
    }
}
