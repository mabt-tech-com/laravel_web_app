<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    public const CANCELLED_ID = 1;
    public const CANCELLED = 'Cancelled';
    public const PENDING_ID = 2;
    public const PENDING = 'Pending';
    public const AWAITING_PAYMENT_ID = 3;
    public const AWAITING_PAYMENT = 'Awaiting Payment';
    public const COMPLETED_ID = 4;
    public const COMPLETED = 'Completed';

    protected $table = 'order_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'label',
        'slug',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
