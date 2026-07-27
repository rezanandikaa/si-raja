<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Sy_import extends Model
{
    use HasUuids;

    protected $table = 'sy_import';

    protected $guarded = ['id'];

    protected $cast = [
        'is_sync' => 'boolean',
        'data' => 'json',
    ];
}
