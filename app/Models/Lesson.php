<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chapter_id',
        'label',
        'position',
        'content',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_lesson', 'lesson_id', 'student_id')->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('position')->latest();
        });

        static::creating(function (Lesson $lesson) {
            $lesson->position = static::where('chapter_id', $lesson->chapter_id)->max('position') + 1;
        });
    }
}
