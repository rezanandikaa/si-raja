<?php

namespace App\Models\System;

use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Model;

class Sy_attachment extends Model
{
    protected $table = 'sy_attachment';

    protected $guarded = ['id'];

    protected $cast = [];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
