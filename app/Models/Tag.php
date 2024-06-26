<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'label',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class)->withTimestamps();
    }
}
