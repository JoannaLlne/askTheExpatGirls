<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case BOOKED = 'Booked';
    case CANCELLED = 'Cancelled';
    case COMPLETED = 'Completed';

    public function label(): string
    {
        return match($this) {
            self::BOOKED => 'Booked',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }
}
