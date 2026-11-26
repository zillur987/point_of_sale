<?php

namespace App\Enums\Inventory;

/** Every reason a stock level can change. Each row of the stock ledger carries one of these. */
enum MovementType: string
{
    case Opening = 'opening';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Purchase = 'purchase';
    case PurchaseReturn = 'purchase_return';
    case Sale = 'sale';
    case SaleReturn = 'sale_return';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Opening stock',
            self::AdjustmentIn => 'Adjustment (in)',
            self::AdjustmentOut => 'Adjustment (out)',
            self::TransferIn => 'Transfer in',
            self::TransferOut => 'Transfer out',
            self::Purchase => 'Purchase',
            self::PurchaseReturn => 'Purchase return',
            self::Sale => 'Sale',
            self::SaleReturn => 'Sale return',
        };
    }
}
