<?php

declare(strict_types=1);

namespace ItmoSoft\Money\Tests;

use ItmoSoft\Money\MoneyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [MoneyServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('itmosoft-money.default_currency', 'USD');
        $app['config']->set('itmosoft-money.rounding_mode', 'HALF_UP');
        $app['config']->set('itmosoft-money.currency_scales', ['USD' => 2, 'JPY' => 0, 'KWD' => 3]);
        $app['config']->set('itmosoft-money.custom_currencies', ['XBT' => ['name' => 'Bitcoin', 'scale' => 8, 'numeric_code' => null]]);
    }
}
