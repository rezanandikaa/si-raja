<?php

namespace App\Models\System;

// use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Sy_user_recovery extends Model
{
    // use SoftDeletes;

    protected $table = 'sy_user_recovery';

    protected $guarded = ['id'];

    protected $cast = [
        'close_flag' => 'boolean',
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     $class = get_called_class();
    //     $class::observe(new AuditTrailObserver());
    // }
}
