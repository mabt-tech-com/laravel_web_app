<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const BEGINNER = 'Beginner';
    public const INTERMEDIATE = 'Intermediate';
    public const EXPERT = 'Expert';

    protected $appends = ['average_rating', 'total_ratings', 'total_sales'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'instructor_id',
        'label',
        'description',
        'level',
        'duration',
        'price',
        'discounted_price',
        'image_id',
        'video_id',
        'is_public',
        'views_count',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function video()
    {
        return $this->belongsTo(File::class, 'video_id');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')->withPivot('expires_at');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1);
    }

    public function getTotalRatingsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getTotalSalesAttribute()
    {
        return $this->orders()->count();
    }

    public function delete_image()
    {
        if ($this->image_id) {
            $file = File::where('id', $this->image_id)->first();
            $file->delete();
        }
    }

    public function delete_video()
    {
        if ($this->video_id) {
            $file = File::where('id', $this->video_id)->first();
            $file->delete();
        }
    }
}
