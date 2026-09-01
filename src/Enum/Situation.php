<?php

namespace App\Enum;

enum Situation: string
{
    case MovingToFrance = 'Moving to France';
    case AlreadyInFrance = 'Already in France';
    case Student = 'Student';
    case Employee = 'Employee';
    case SelfEmployed = 'Self-employed';
    case Retired = 'Retired';
    case FamilyMember = 'Family member';

    public function label(): string
    {
        return match($this) {
            self::MovingToFrance => 'Moving to France',
            self::AlreadyInFrance => 'Already in France',
            self::Student => 'Student',
            self::Employee => 'Employee',
            self::SelfEmployed => 'Self-employed',
            self::Retired => 'Retired',
            self::FamilyMember => 'Family member',
        };
    }
}
