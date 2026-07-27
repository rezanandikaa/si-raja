<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Sy_log_activity extends Model
{
    use HasUuids;

    protected $table = 'sy_log_activity';

    protected $guarded = ['id'];
}
