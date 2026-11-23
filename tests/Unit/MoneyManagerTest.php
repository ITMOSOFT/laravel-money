<?php

declare(strict_types=1);

namespace ItmoSoft\Money\Tests\Unit;

use Brick\Math\RoundingMode;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\RationalMoney;
use ItmoSoft\Money\Services\CurrencyPrecisionRegistry;
use ItmoSoft\Money\Services\MoneyManager;
use ItmoSoft\Money\Tests\TestCase;
use InvalidArgumentException;

final class MoneyManagerTest extends TestCase
{
    public function test_it_creates_money_using_configured_currency_precision(): void
    {
        $manager = app(MoneyManager::class);
        self::assertSame('USD 12.30', (string) $manager->of('12.3', 'USD'));
        self::assertSame(2, $manager->precision('USD'));
    }

    public function test_it_supports_zero_and_three_decimal_currencies(): void
    {
        $manager = app(MoneyManager::class);
        self::assertSame('JPY 125', (string) $manager->of('125.4', 'JPY'));
        self::assertSame('KWD 1.234', (string) $manager->of('1.2344', 'KWD'));
    }

    public function test_it_uses_half_up_rounding_at_the_currency_boundary(): void
    {
        $manager = app(MoneyManager::class);
        self::assertSame('USD 1.01', (string) $manager->of('1.005', 'USD'));
        self::assertSame('USD 20.00', (string) $manager->of('19.9999', 'USD'));
    }

    public function test_it_supports_configured_custom_currencies(): void
    {
        self::assertSame('XBT 0.12345678', (string) app(MoneyManager::class)->of('0.12345678', 'XBT'));
    }

    public function test_it_creates_money_from_minor_units(): void
    {
        self::assertSame('USD 12.34', (string) app(MoneyManager::class)->ofMinor('1234', 'USD'));
    }

    public function test_exact_intermediate_calculations_do_not_round_until_settlement(): void
    {
        $manager = app(MoneyManager::class);
        $exact = $manager->exact('10.00', 'USD')->dividedBy(3)->multipliedBy(3);
        self::assertInstanceOf(RationalMoney::class, $exact);
        self::assertSame('USD 10.00', (string) $manager->settle($exact));
    }

    public function test_it_calculates_percentages_and_rounds_once(): void
    {
        $manager = app(MoneyManager::class);
        self::assertSame('USD 3.00', (string) $manager->percentage($manager->exact('19.99', 'USD'), '15'));
    }

    public function test_it_sums_without_intermediate_currency_rounding(): void
    {
        $manager = app(MoneyManager::class);
        $total = $manager->sum([
            $manager->exact('0.335', 'USD'),
            $manager->exact('0.335', 'USD'),
            $manager->exact('0.335', 'USD'),
        ], 'USD');
        self::assertSame('USD 1.01', (string) $total);
    }

    public function test_it_rejects_currency_mismatches_in_sums(): void
    {
        $manager = app(MoneyManager::class);
        $this->expectException(InvalidArgumentException::class);
        $manager->sum([$manager->of('1.00', 'USD'), $manager->of('1.00', 'JPY')], 'USD');
    }

    public function test_it_rejects_float_inputs(): void
    {
        $this->expectException(\TypeError::class);
        app(MoneyManager::class)->of(1.25, 'USD');
    }

    public function test_it_rejects_invalid_scale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(MoneyManager::class)->of('1.00', 'USD', 19);
    }

    public function test_it_rejects_unknown_currency_codes_even_when_querying_precision(): void
    {
        $this->expectException(UnknownCurrencyException::class);
        app(MoneyManager::class)->precision('ZZZ');
    }

    public function test_currency_registry_validates_overrides(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CurrencyPrecisionRegistry(['USD' => -1]);
    }

    public function test_settlement_can_override_the_rounding_mode(): void
    {
        $manager = app(MoneyManager::class);
        $exact = $manager->exact('1.005', 'USD');
        self::assertSame('USD 1.00', (string) $manager->settle($exact, null, RoundingMode::HalfDown));
    }
}
