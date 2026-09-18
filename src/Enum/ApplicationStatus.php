<?php

namespace App\Enum;

enum ApplicationStatus: string
{
    case PENDING = 'Pending';
    case INPROGRESS = 'In Progress';
    case PROCESSED = 'Processed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::INPROGRESS => 'In Progress',
            self::PROCESSED => 'Processed',
        };
    }
}
