<?php

namespace App\Models\System;

use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sy_preference extends Model
{
    use SoftDeletes;

    protected $table = 'sy_preference';

    protected $guarded = ['id'];

    protected $cast = [];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
