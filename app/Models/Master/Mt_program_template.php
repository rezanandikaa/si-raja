<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuditTrailObserver;

class Mt_program_template extends Model
{
    use SoftDeletes;

    protected $table = 'mt_program_template';

    protected $guarded = ['id'];

    protected $cast = [
        // 'access_module' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
