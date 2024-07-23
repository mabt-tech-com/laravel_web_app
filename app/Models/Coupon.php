<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'title',
        'description',
        'discount_percentage',
        'discount_value',
        'applicable_if_total_is_above',
        'max_usage',
        'active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function apply_coupon($total)
    {
        if (!$this->active) {
            return 'Coupon is not active.';
        }
        if ($this->applicable_if_total_is_above > $total) {
            return 'The total amount of your purchase does not meet the minimum threshold required to use this coupon.';
        }
        if ($this->max_usage <= $this->orders()->count()) {
            return 'Coupon has already been used the maximum number of times.';
        }
        if (now()->lessThan($this->starts_at)) {
            return 'Coupon is not yet active.';
        }
        if (now()->greaterThan($this->expires_at)) {
            return 'Coupon has expired.';
        }

        return true;
    }
}
