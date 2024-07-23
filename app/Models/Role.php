<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    public const STUDENT = 1;

    public const INSTRUCTOR = 2;

    public const CONTENT_MANAGER = 3;

    public const ADMIN = 4;

    public const SUPER_ADMIN = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'label',
        'description',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'permission_role')->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withPivot('company_id')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

}
