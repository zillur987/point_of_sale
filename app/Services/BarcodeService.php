<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Generates internal EAN-13 barcodes for variants that do not carry a manufacturer barcode.
 * Prefix 200-299 is reserved by GS1 for in-store/internal use, so it never clashes with real products.
 */
final class BarcodeService
{
    private const INTERNAL_PREFIX = '200';

    public function forVariant(int $variantId): string
    {
        $twelve = self::INTERNAL_PREFIX.str_pad((string) $variantId, 9, '0', STR_PAD_LEFT);

        return $twelve.$this->checkDigit($twelve);
    }

    public function checkDigit(string $twelveDigits): int
    {
        $sum = 0;

        foreach (str_split($twelveDigits) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10;
    }
}
