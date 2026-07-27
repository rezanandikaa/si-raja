<?php

namespace App\Models\System;

use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sy_option extends Model
{
    use SoftDeletes;

    protected $table = 'sy_option';

    protected $guarded = ['id'];

    protected $cast = [
        // 'is_sync' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
