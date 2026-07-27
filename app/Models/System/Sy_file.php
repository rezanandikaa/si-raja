<?php

namespace App\Models\System;

use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Model;

class Sy_file extends Model
{
    protected $table = 'sy_file';

    protected $guarded = ['id'];

    protected $cast = [
        'is_sync' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
