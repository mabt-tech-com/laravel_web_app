<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    public const USERS_LIST = 1;
    public const USERS_CREATE = 2;
    public const USERS_VIEW = 3;
    public const USERS_UPDATE = 4;
    public const USERS_DELETE = 5;
    public const USERS_RESTORE = 6;
    public const USERS_FORCE_DELETE = 7;

    public const COMPANIES_LIST = 10;
    public const COMPANIES_CREATE = 11;
    public const COMPANIES_VIEW = 12;
    public const COMPANIES_UPDATE = 13;
    public const COMPANIES_DELETE = 14;
    public const COMPANIES_RESTORE = 15;
    public const COMPANIES_FORCE_DELETE = 16;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'label',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot('company_id')->withTimestamps();
    }
}
