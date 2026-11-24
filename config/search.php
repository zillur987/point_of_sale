<?php

declare(strict_types=1);

/**
 * Public property-search configuration (the banner search bar).
 *
 * Price ranges are config, not DB rows: change them here and the search bar
 * (served by GET /api/v1/public/properties/options) updates without a frontend deploy.
 * `value` is what the frontend sends as `?price=`; min/max are inclusive, null = open-ended.
 */
return [
    'currency' => env('SEARCH_CURRENCY', 'AED'),

    'price_ranges' => [
        ['value' => '0-500k',   'label' => 'Up to 500K', 'min' => null,      'max' => 500_000],
        ['value' => '500k-1m',  'label' => '500K – 1M',  'min' => 500_000,   'max' => 1_000_000],
        ['value' => '1m-3m',    'label' => '1M – 3M',    'min' => 1_000_000, 'max' => 3_000_000],
        ['value' => '3m+',      'label' => '3M+',        'min' => 3_000_000, 'max' => null],
    ],

    /** Bedroom counts at or above this collapse into one "N+" option / filter. */
    'bedrooms_plus_from' => 5,

    'per_page' => 12,
    'max_per_page' => 48,
];
