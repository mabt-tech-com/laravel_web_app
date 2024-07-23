<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_INTERNAL = '1';

    public const TYPE_EXTERNAL = '2';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'file_name',
        'url',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::deleting(function ($file) {
            if (Storage::exists('public/files/' . $file->file_name)) {
                Storage::delete('public/files/' . $file->file_name);
                insert_in_history_table('deleted', $file->id, $file->getTable());
            }
        });
    }
}
