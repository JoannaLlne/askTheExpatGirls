<?php

namespace App\Enum;

enum ApplicationStatus: string
{
    case NEW = 'New';
    case PENDING = 'Pending';
    case IN_PROGRESS = 'In Progress';
    case PROCESSED = 'Processed';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::PROCESSED => 'Processed',
        };
    }
}
