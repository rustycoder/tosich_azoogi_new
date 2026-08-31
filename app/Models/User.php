<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'user_type', 'status'])]
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
            'user_type' => UserType::class,
            'status' => Status::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function isStaff(): bool
    {
        return $this->user_type === UserType::Staff;
    }

    public function isCustomer(): bool
    {
        return $this->user_type === UserType::Customer;
    }

    public function isTrader(): bool
    {
        return $this->user_type === UserType::Trader;
    }
}
