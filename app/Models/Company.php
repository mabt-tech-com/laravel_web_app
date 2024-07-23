<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['number_of_ratings', 'average_rating'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'label',
        'description',
        'currency',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Category::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function reviews()
    {
        return $this->hasMany(Category::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Category::class);
    }

    public function certifications()
    {
        return $this->hasMany(Category::class);
    }

    public function getNumberOfRatingsAttribute()
    {
        $this->load('trainings');
        return $this->trainings->count('number_of_ratings');
    }

    public function getAverageRatingAttribute()
    {
        $this->load('trainings');
        return round($this->trainings->avg('average_rating'), 1);
    }
}
