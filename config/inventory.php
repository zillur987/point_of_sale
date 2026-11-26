<?php

return [
    /*
    | When false (default) any movement that would drive a stock level below zero is rejected.
    | The POS sale flow (next step) reads the same flag, so overselling is a single switch.
    */
    'allow_negative_stock' => (bool) env('INVENTORY_ALLOW_NEGATIVE_STOCK', false),
];
