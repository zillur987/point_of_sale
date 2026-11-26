<?php

namespace App\Exceptions\Inventory;

final class InsufficientStockException extends InventoryException
{
    public static function for(string $label, string $available, string $requested): self
    {
        return new self("Insufficient stock for {$label}: available {$available}, requested {$requested}.");
    }
}
