<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuditTrailObserver;

class Tr_program_sifat_bantuan extends Model
{
    use SoftDeletes;

    protected $table = 'tr_program_sifat_bantuan';

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
