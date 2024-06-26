<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestionOption extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'quiz_question_options';

    protected $fillable = [
        'quiz_question_id',
        'label',
        'is_correct',
    ];

    public function quiz_question()
    {
        return $this->belongsTo(QuizQuestion::class);
    }

    public function quiz_question_option_items()
    {
        return $this->hasMany(QuizQuestionOptionItem::class);
    }

    public function quiz_student_attempts()
    {
        return $this->belongsToMany(QuizStudentAttempt::class, 'quiz_student_answers', 'quiz_question_option_id', 'quiz_student_attempt_id')
            ->withPivot('attempt', 'quiz_question_id')
            ->withTimestamps();
    }
}
