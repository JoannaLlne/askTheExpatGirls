<?php
declare(strict_types=1);

namespace App\Enum;

enum ApplicationStatus: string
{
    case NEW = 'New';
    case PENDING = 'Pending';
    case IN_PROGRESS = 'In Progress';
    case PROCESSED = 'Processed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::PROCESSED => 'Processed',
        };
    }

    public function step(): int
    {
        return match ($this) {
            self::NEW => 1,
            self::PENDING => 2,
            self::IN_PROGRESS => 3,
            self::PROCESSED => 4,
        };
    }
}
