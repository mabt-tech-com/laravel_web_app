<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use AuthenticationLoggable;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'role_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'image_id',
        'password',
        'birthday',
        'bio',
        'blocked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getFullnameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->role->permissions()->where('company_id', $this->company_id)->get();
    }

    public function trainings_as_instructor()
    {
        return $this->hasMany(Training::class, 'instructor_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'student_lesson', 'student_id', 'lesson_id')->withTimestamps();
    }

    public function image()
    {
        return $this->belongsTo(File::class);
    }

    public function images_uploaded()
    {
        return $this->hasMany(File::class);
    }

    public function wishlist()
    {
        return $this->hasOne(Order::class, 'student_id')->where('orders.type', 1);
    }

    public function cart()
    {
        return $this->hasOne(Order::class, 'student_id')->where('orders.type', 2);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'student_id')->where('orders.type', 3);
    }

    public function trainings()
    {
        $trainings = collect([]);
        foreach ($this->orders as $order) {
            $order->load('trainings');

            foreach ($order->trainings as $training) {
                $trainings->push($training);
            }
        }

        return $trainings;
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_student_attempts', 'student_id', 'quiz_id')
            ->withPivot('attempt', 'finished_at')
            ->using(QuizStudentAttempt::class)
            ->withTimestamps();
    }

    public function certified_trainings()
    {
        return $this->belongsToMany(Training::class, 'certifications', 'student_id', 'training_id')->withTimestamps();
    }

    public function certified_quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'certifications', 'student_id', 'quiz_id')->withTimestamps();
    }

    public function delete_image()
    {
        if ($this->image_id) {
            $file = File::findOrFail($this->image_id);
            $file->delete();
        }
    }

    public function delete_uploaded_images()
    {
        if ($this->images_uploaded->count() > 0) {
            $files = File::whereIn('id', $this->images_uploaded->pluck('id'))->get();
            foreach ($files as $file) {
                $file->delete();
            }
        }
    }


    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

}
