<?php

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Customer = 'customer';
    case Trader = 'trader';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Staff => 'Staff',
            self::Customer => 'Customer',
            self::Trader => 'Trader',
        };
    }
}
