<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestionType extends Model
{
    use HasFactory;

    public const TRUE_OR_FALSE_ID = 1;
    public const TRUE_OR_FALSE = 'True or False';
    public const SINGLE_ANSWER_ID = 2;
    public const SINGLE_ANSWER = 'Single Answer';
    public const MULTIPLE_ANSWER_ID = 3;
    public const MULTIPLE_ANSWER = 'Multiple Answer';
    public const DRAG_AND_DROP_ID = 4;
    public const DRAG_AND_DROP = 'Drag & Drop';

    protected $fillable = [
        'label',
    ];

    public function quiz_question_type()
    {
        return $this->belongsTo(QuizQuestionType::class);
    }

    public function quiz_questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

}
