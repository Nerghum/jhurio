<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email',
    'phone', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->id === 1 || $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return (int) $this->id === 1 || $this->role === 'admin' || $this->role === 'superadmin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->latest();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
