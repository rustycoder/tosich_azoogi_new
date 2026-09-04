<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }
}
