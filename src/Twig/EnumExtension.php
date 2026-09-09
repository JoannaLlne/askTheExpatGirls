<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EnumExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('enum_cases', [$this, 'getCases']),
        ];
    }

    public function getCases(string $enumClass): array
    {
        return $enumClass::cases();
    }
}
