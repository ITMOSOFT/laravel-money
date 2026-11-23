<?php

declare(strict_types=1);

namespace ItmoSoft\Money\Services;

use Brick\Money\Currency;
use InvalidArgumentException;

final class CurrencyPrecisionRegistry
{
    /** @var array<string, int> */
    private readonly array $overrides;

    /** @var array<string, array{name: string, scale: int, numeric_code?: int|null}> */
    private readonly array $customCurrencies;

    /**
     * @param array<string, int> $overrides
     * @param array<string, array{name: string, scale: int, numeric_code?: int|null}> $customCurrencies
     */
    public function __construct(array $overrides = [], array $customCurrencies = [])
    {
        $normalizedOverrides = [];
        foreach ($overrides as $code => $scale) {
            $code = strtoupper(trim((string) $code));
            self::assertCode($code);
            self::assertScale($scale, $code);
            $normalizedOverrides[$code] = $scale;
        }

        $normalizedCustom = [];
        foreach ($customCurrencies as $code => $definition) {
            $code = strtoupper(trim((string) $code));
            self::assertCode($code);
            $name = $definition['name'] ?? null;
            $scale = $definition['scale'] ?? null;
            if (! is_string($name) || trim($name) === '') {
                throw new InvalidArgumentException("Custom currency [{$code}] requires a non-empty name.");
            }
            self::assertScale($scale, $code);
            $normalizedCustom[$code] = [
                'name' => $name,
                'scale' => $scale,
                'numeric_code' => $definition['numeric_code'] ?? null,
            ];
        }

        $this->overrides = $normalizedOverrides;
        $this->customCurrencies = $normalizedCustom;
    }

    public function currency(string $code): Currency
    {
        $code = strtoupper(trim($code));
        self::assertCode($code);

        if (isset($this->customCurrencies[$code])) {
            $definition = $this->customCurrencies[$code];
            return new Currency($code, (int) ($definition['numeric_code'] ?? 0), $definition['name'], $definition['scale']);
        }

        return Currency::of($code);
    }

    public function precision(string $code): int
    {
        $code = strtoupper(trim($code));
        self::assertCode($code);

        $currency = $this->currency($code);

        if (isset($this->overrides[$code])) {
            return $this->overrides[$code];
        }

        if (isset($this->customCurrencies[$code])) {
            return $this->customCurrencies[$code]['scale'];
        }

        return $currency->getDefaultFractionDigits();
    }

    private static function assertCode(string $code): void
    {
        if (! preg_match('/^[A-Z]{3}$/', $code)) {
            throw new InvalidArgumentException("Invalid three-letter currency code [{$code}].");
        }
    }

    private static function assertScale(mixed $scale, string $code): void
    {
        if (! is_int($scale) || $scale < 0 || $scale > 18) {
            throw new InvalidArgumentException("Currency scale for [{$code}] must be an integer between 0 and 18.");
        }
    }
}
