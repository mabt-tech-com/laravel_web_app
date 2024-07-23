<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuizStudentAttempt extends Pivot
{
    protected $table = 'quiz_student_attempts';

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quiz_questions()
    {
        return $this->belongsToMany(QuizQuestion::class, 'quiz_student_answers', 'quiz_student_attempt_id', 'quiz_question_id')
            ->withPivot('quiz_question_option_id', 'quiz_question_option_item_id')
            ->withTimestamps();
    }

    public function quiz_question_options()
    {
        return $this->belongsToMany(QuizQuestionOption::class, 'quiz_student_answers', 'quiz_student_attempt_id', 'quiz_question_option_id')
            ->withPivot('quiz_question_id', 'quiz_question_option_item_id')
            ->withTimestamps();
    }

    public function quiz_question_option_items()
    {
        return $this->belongsToMany(QuizQuestionOptionItem::class, 'quiz_student_answers', 'quiz_student_attempt_id', 'quiz_question_option_item_id')
            ->withPivot('quiz_question_id', 'quiz_question_option_id')
            ->withTimestamps();
    }
}
