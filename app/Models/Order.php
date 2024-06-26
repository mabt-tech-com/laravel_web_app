<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const WISHLIST = 1;
    public const CART = 2;
    public const ORDER = 3;

    protected $fillable = [
        'student_id',
        'type',
        'order_status_id',
        'coupon_id',
        'voucher_id',
        'notes',
    ];

    protected $appends = ['discounted_price', 'total_price'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function order_status()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'order_items')->withPivot('expires_at');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'order_items')->withPivot('expires_at');
    }

    public function scopefilterType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getDiscountedPriceAttribute()
    {
        if ($this->coupon_id) {
            $coupon = Coupon::findOrFail($this->coupon_id);

            if ($coupon->discount_percentage) {
                return $this->total_price - ($this->total_price * ($coupon->discount_percentage / 100));
            } else if ($coupon->discount_value) {
                return $this->total_price - $coupon->discount_value;
            }
        }

        return $this->total_price;
    }

    public function getTotalPriceAttribute()
    {
        return $this->trainings->sum('price') + $this->quizzes->sum('price');
    }

    public function trainings_from_orders($orders)
    {
        $trainings = [];
        foreach ($orders as $order) {

            $order->load('trainings.instructor', 'trainings.image', 'trainings.chapters.lessons');

            foreach ($order->trainings as $training) {
                if ($training->pivot->expires_at && Carbon::parse($training->pivot->expires_at)->isAfter(now())) {
                    $trainings[] = $training;
                }
            }
        }
        return $trainings;
    }

    public function quizzes_from_orders($orders)
    {
        $quizzes = [];
        foreach ($orders as $order) {

            $order->load('quizzes');

            foreach ($order->quizzes as $quiz) {
                if ($quiz->pivot->expires_at && Carbon::parse($quiz->pivot->expires_at)->isAfter(now())) {
                    $quizzes[] = $quiz;
                }
            }
        }
        return $quizzes;
    }

    public function students_from_orders($orders)
    {
        $students = [];
        foreach ($orders as $order) {
            $students[] = User::with('lessons')->findOrFail($order->student_id);
        }
        return $students;
    }
}
