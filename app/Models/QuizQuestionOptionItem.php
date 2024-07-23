<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestionOptionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'quiz_question_option_id',
        'label',
    ];

    public function quiz_question_option()
    {
        return $this->belongsTo(QuizQuestionOption::class);
    }
}
