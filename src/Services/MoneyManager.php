<?php

declare(strict_types=1);

namespace ItmoSoft\Money\Services;

use Brick\Math\RoundingMode;
use Brick\Money\Context\AutoContext;
use Brick\Money\Context\CustomContext;
use Brick\Money\Money;
use Brick\Money\RationalMoney;
use InvalidArgumentException;

final class MoneyManager
{
    public function __construct(
        private readonly CurrencyPrecisionRegistry $currencies,
        private readonly RoundingMode $roundingMode = RoundingMode::HALF_UP,
        private readonly string $defaultCurrency = 'USD',
    ) {
    }

    /** Floats are deliberately excluded; pass decimal strings for exact input. */
    public function of(int|string $amount, ?string $currency = null, ?int $scale = null): Money
    {
        $currency ??= $this->defaultCurrency;
        $currencyObject = $this->currencies->currency($currency);
        $scale ??= $this->currencies->precision($currency);
        $this->assertScale($scale);

        return Money::of($amount, $currencyObject, new CustomContext($scale), $this->roundingMode);
    }

    public function ofMinor(int|string $minorAmount, ?string $currency = null, ?int $scale = null): Money
    {
        $currency ??= $this->defaultCurrency;
        $currencyObject = $this->currencies->currency($currency);
        $scale ??= $this->currencies->precision($currency);
        $this->assertScale($scale);

        return Money::ofMinor($minorAmount, $currencyObject, new CustomContext($scale), $this->roundingMode);
    }

    /** Create an exact rational amount; no currency-scale rounding occurs yet. */
    public function exact(int|string $amount, ?string $currency = null): RationalMoney
    {
        return Money::of(
            $amount,
            $this->currencies->currency($currency ?? $this->defaultCurrency),
            new AutoContext(),
        )->toRational();
    }

    public function settle(
        Money|RationalMoney $amount,
        ?int $scale = null,
        ?RoundingMode $roundingMode = null,
    ): Money {
        $currency = $amount->getCurrency()->getCurrencyCode();
        $scale ??= $this->currencies->precision($currency);
        $this->assertScale($scale);

        return Money::of(
            $amount->getAmount(),
            $amount->getCurrency(),
            new CustomContext($scale),
            $roundingMode ?? $this->roundingMode,
        );
    }

    public function percentage(
        Money|RationalMoney $base,
        int|string $percentage,
        ?int $scale = null,
        ?RoundingMode $roundingMode = null,
    ): Money {
        $exact = $base instanceof Money ? $base->toRational() : $base;
        return $this->settle($exact->multipliedBy($percentage)->dividedBy(100), $scale, $roundingMode);
    }

    /** @param iterable<Money|RationalMoney|int|string> $values */
    public function sum(
        iterable $values,
        ?string $currency = null,
        ?int $scale = null,
        ?RoundingMode $roundingMode = null,
    ): Money {
        $currency ??= $this->defaultCurrency;
        $currencyObject = $this->currencies->currency($currency);
        $total = $this->exact('0', $currency);

        foreach ($values as $value) {
            if ($value instanceof Money || $value instanceof RationalMoney) {
                if (! $value->getCurrency()->isEqualTo($currencyObject)) {
                    throw new InvalidArgumentException('All values in a money sum must use the same currency.');
                }
                $total = $total->plus($value instanceof Money ? $value->toRational() : $value);
            } else {
                $total = $total->plus($this->exact($value, $currency));
            }
        }

        return $this->settle($total, $scale, $roundingMode);
    }

    public function precision(string $currency): int
    {
        return $this->currencies->precision($currency);
    }

    private function assertScale(int $scale): void
    {
        if ($scale < 0 || $scale > 18) {
            throw new InvalidArgumentException('Money scale must be between 0 and 18.');
        }
    }
}
