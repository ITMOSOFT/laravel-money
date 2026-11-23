<?php

return [
    'default_currency' => env('ITMOSOFT_MONEY_DEFAULT_CURRENCY', 'USD'),
    // ISO 4217 precision is used unless explicitly overridden here.
    'custom_currencies' => [
        // 'XBT' => ['name' => 'Bitcoin', 'scale' => 8, 'numeric_code' => null],
    ],
    'currency_scales' => [
        // 'USD' => 2,
        // 'JPY' => 0,
        // 'KWD' => 3,
    ],
    'rounding_mode' => env('ITMOSOFT_MONEY_ROUNDING_MODE', 'HALF_UP'),
];
