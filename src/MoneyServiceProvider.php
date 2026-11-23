<?php

declare(strict_types=1);

namespace ItmoSoft\Money;

use Brick\Math\RoundingMode;
use Illuminate\Support\ServiceProvider;
use ItmoSoft\Money\Services\CurrencyPrecisionRegistry;
use ItmoSoft\Money\Services\MoneyManager;
use InvalidArgumentException;

final class MoneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/itmosoft-money.php', 'itmosoft-money');

        $this->app->singleton(CurrencyPrecisionRegistry::class, fn ($app) => new CurrencyPrecisionRegistry(
            (array) $app['config']->get('itmosoft-money.currency_scales', []),
            (array) $app['config']->get('itmosoft-money.custom_currencies', [])
        ));

        $this->app->singleton(MoneyManager::class, function ($app): MoneyManager {
            $mode = strtoupper((string) $app['config']->get('itmosoft-money.rounding_mode', 'HALF_UP'));

            return new MoneyManager(
                $app->make(CurrencyPrecisionRegistry::class),
                self::roundingMode($mode),
                strtoupper((string) $app['config']->get('itmosoft-money.default_currency', 'USD')),
            );
        });

        $this->app->alias(MoneyManager::class, 'itmosoft.money');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/itmosoft-money.php' => config_path('itmosoft-money.php'),
        ], 'itmosoft-money-config');
    }

    private static function roundingMode(string $mode): RoundingMode
    {
        return match ($mode) {
            'UP' => RoundingMode::Up,
            'DOWN' => RoundingMode::Down,
            'CEILING' => RoundingMode::Ceiling,
            'FLOOR' => RoundingMode::Floor,
            'HALF_UP' => RoundingMode::HalfUp,
            'HALF_DOWN' => RoundingMode::HalfDown,
            'HALF_EVEN' => RoundingMode::HalfEven,
            'UNNECESSARY' => RoundingMode::Unnecessary,
            default => throw new InvalidArgumentException("Unsupported money rounding mode [{$mode}]."),
        };
    }
}
