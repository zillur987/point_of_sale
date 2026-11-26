<?php

namespace App\Enums\Inventory;

enum AdjustmentReason: string
{
    case OpeningStock = 'opening_stock';
    case Damaged = 'damaged';
    case Expired = 'expired';
    case Lost = 'lost';
    case Found = 'found';
    case Correction = 'correction';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OpeningStock => 'Opening stock',
            self::Damaged => 'Damaged',
            self::Expired => 'Expired',
            self::Lost => 'Lost / stolen',
            self::Found => 'Found',
            self::Correction => 'Stock count correction',
            self::Other => 'Other',
        };
    }
}
