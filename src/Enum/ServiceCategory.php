<?php

namespace App\Enum;

enum ServiceCategory: string
{
    case STAYIMMIGRATION = 'Stay & Immigration';
    case WORKSTUDY = 'Work & Study';
    case HEALTHCARE = 'Healthcare';
    case CAR_TRANSPORT = 'Car & Transport';
    case OTHERS = 'Others';

    public function label(): string
    {
        return match($this) {
            self::STAYIMMIGRATION => 'Stay & Immigration',
            self::WORKSTUDY => 'Work & Study',
            self::HEALTHCARE => 'Healthcare',
            self::CAR_TRANSPORT => 'Car & Transport',
            self::OTHERS => 'Others',
        };
    }
}
