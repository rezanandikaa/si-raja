<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Master\Mt_organization;
use App\Observers\AuditTrailObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'mt_user';

    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active_flag' => 'boolean',
    ];

    public function getNameWithTitleAttribute()
    {
        $prefixTitle = ($this->prefix_title == '' ? '' : $this->prefix_title . ' ');
        $suffixTitle = $this->suffix_title == '' ? '' : ', ' . $this->suffix_title;
        $fullName = $prefixTitle . $this->name . $suffixTitle;
        return $fullName;
    }

    public function getRealOrganizationIdAttribute()
    {
        $organization_id = 0;
        $record = Mt_organization::find($this->organization_id);
        if ($record) {
            $organization_id = $record->parent_id == 0 ? $record->id : $record->parent_id;
        }
        return $organization_id;
    }

    public function getOrganizationParentIdAttribute()
    {
        $parent_organization_id = 0;
        $record = Mt_organization::find($this->organization_id);
        if ($record) {
            $parent_organization_id = $record->parent_id;
        }
        return $parent_organization_id;
    }

    protected static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $class::observe(new AuditTrailObserver());
    }
}
