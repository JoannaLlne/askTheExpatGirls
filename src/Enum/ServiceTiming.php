<?php

namespace App\Enum;

enum ServiceTiming: string
{
    case BEFOREMOVING = 'Before moving to France';
    case AFTERARRIVAL = 'After arrival';


    public function label(): string
    {
        return match($this) {
            self::BEFOREMOVING => 'Before moving to France',
            self::AFTERARRIVAL => 'After arrival',
        };
    }
}
