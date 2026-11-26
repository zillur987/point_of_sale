<?php

namespace App\Support;

/**
 * Quantities are DECIMAL(15,3) in the database and travel as fixed-scale strings ("12.500"),
 * so no float ever reaches a stock balance. The math itself is done by the database.
 */
final class Quantity
{
    public const SCALE = 3;

    public static function normalize(string|int|float $value): string
    {
        return number_format((float) $value, self::SCALE, '.', '');
    }

    public static function negate(string $value): string
    {
        $value = self::normalize($value);

        if ((float) $value === 0.0) {
            return $value;
        }

        return str_starts_with($value, '-') ? substr($value, 1) : '-'.$value;
    }

    public static function isWhole(string|int|float $value): bool
    {
        return fmod((float) self::normalize($value), 1.0) === 0.0;
    }
}
