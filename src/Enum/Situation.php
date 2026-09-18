<?php

namespace App\Enum;

enum Situation: string
{
    case MOVINGTOFRANCE = 'Moving to France';
    case ALREADYINFRANCE = 'Already in France';
    case STUDENT = 'Student';
    case EMPLOYEE = 'Employee';
    case SELFEMPLOYED = 'Self-employed';
    case RETIRED = 'Retired';
    case FAMILYMEMBER = 'Family member';

    public function label(): string
    {
        return match($this) {
            self::MOVINGTOFRANCE => 'Moving to France',
            self::ALREADYINFRANCE => 'Already in France',
            self::STUDENT => 'Student',
            self::EMPLOYEE => 'Employee',
            self::SELFEMPLOYED => 'Self-employed',
            self::RETIRED => 'Retired',
            self::FAMILYMEMBER => 'Family member',
        };
    }
}
