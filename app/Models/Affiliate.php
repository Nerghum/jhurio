<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'code',
        'status',
        'balance',
        'pending_balance',
        'lifetime_earnings',
        'payout_method',
        'payout_account',
        'notes',
        'rejection_reason',
        'approved_at',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'lifetime_earnings' => 'decimal:2',
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public static function generateUniqueCode(string $name = ''): string
    {
        do {
            $prefix = 'JH';
            $random = strtoupper(Str::random(6));
            $code = "{$prefix}-{$random}";
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
