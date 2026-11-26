<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductType: string
{
    case Single = 'single';
    case Variable = 'variable';
}
