<?php

namespace App\Enum;

enum ServiceCategory: string
{
    case StayImmigration = 'Stay & Immigration';
    case WorkStudy = 'Work & Study';
    case Healthcare = 'Healthcare';
    case Other = 'Other';

    public function label(): string
    {
        return match($this) {
            self::StayImmigration => 'Stay & Immigration',
            self::WorkStudy => 'Work & Study',
            self::Healthcare => 'Healthcare',
            self::Other => 'Other',
        };
    }
}
