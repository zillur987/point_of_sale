<?php

namespace App\Enums\Inventory;

enum AdjustmentDirection: string
{
    case In = 'in';
    case Out = 'out';

    public function isInbound(): bool
    {
        return $this === self::In;
    }
}
