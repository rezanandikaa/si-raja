<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuditTrailObserver;

class Tr_program_realization extends Model
{
    use SoftDeletes;

    protected $table = 'tr_program_realization';

    protected $guarded = ['id'];

    protected $cast = [
        // 'active_flag' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
