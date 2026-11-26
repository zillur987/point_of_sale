<?php

namespace App\Exceptions\Inventory;

final class WarehouseRuleException extends InventoryException
{
    public static function cannotDeleteDefault(): self
    {
        return new self('The default warehouse cannot be deleted. Make another warehouse the default first.');
    }

    public static function cannotDeactivateDefault(): self
    {
        return new self('The default warehouse cannot be deactivated. Make another warehouse the default first.');
    }

    public static function hasStockHistory(): self
    {
        return new self('This warehouse has stock history and cannot be deleted. Deactivate it instead.');
    }
}
