<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'active',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'voucher_training');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'voucher_quiz');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
