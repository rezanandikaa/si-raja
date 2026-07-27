<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuditTrailObserver;

class Mt_budget_source extends Model
{
    use SoftDeletes;

    protected $table = 'mt_budget_source';

    protected $guarded = ['id'];

    protected $cast = [
        // 'properties' => 'json',
        // 'active_flag' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
