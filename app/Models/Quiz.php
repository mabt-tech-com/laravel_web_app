<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quizzes';

    protected $appends = ['average_rating', 'total_ratings', 'total_sales'];

    protected $fillable = [
        'company_id',
        'training_id',
        'chapter_id',
        'label',
        'description',
        'duration',
        'break_interval',
        'break_duration_in_mins',
        'max_attempts',
        'passing_percentage',
        'is_published',
        'price',
        'discounted_price',
    ];

    public function scopeIsQuiz($query)
    {
        $query->whereNotNull('chapter_id');
    }

    public function scopeIsExam($query)
    {
        $query->whereNull('chapter_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quiz_questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'quiz_student_attempts', 'quiz_id', 'student_id')
            ->withPivot('attempt', 'finished_at')
            ->using(QuizStudentAttempt::class)
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')->withPivot('expires_at');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class);
    }

    public function certified_students()
    {
        return $this->belongsToMany(User::class, 'certifications', 'quiz_id', 'student_id')->withTimestamps();
    }

    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
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
}
