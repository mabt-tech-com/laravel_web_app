<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'quiz_question_type_id',
        'label',
        'tip',
        'explanation',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function quiz_question_type()
    {
        return $this->belongsTo(QuizQuestionType::class);
    }

    public function quiz_question_options()
    {
        return $this->hasMany(QuizQuestionOption::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_quizzes', 'quiz_question_id', 'student_id')
            ->withPivot('attempt', 'quiz_id', 'quiz_question_option_id')
            ->withTimestamps();
    }

    public function quiz_student_attempts()
    {
        return $this->belongsToMany(QuizStudentAttempt::class, 'quiz_student_answers', 'quiz_question_id', 'quiz_student_attempt_id')
            ->withPivot('attempt', 'quiz_question_option_id')
            ->withTimestamps();
    }
}
