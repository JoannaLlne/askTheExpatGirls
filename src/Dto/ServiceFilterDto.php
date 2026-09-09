<?php
// src/Dto/ServiceFilterDto.php
namespace App\Dto;

use App\Enum\ServiceCategory;
use App\Enum\ServiceTiming;

class ServiceFilterDto
{
    public ?ServiceTiming $timing = null;

    /** @var ServiceCategory[] */
    public array $categories = [];
}
