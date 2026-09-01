<?php

namespace App\Enum;

enum ServiceTiming: string
{
    case BeforeMoving = 'Before moving to France';
    case AfterArrival = 'After arrival';


    public function label(): string
    {
        return match($this) {
            self::BeforeMoving => 'Before moving to France',
            self::AfterArrival => 'After arrival',
        };
    }
}
