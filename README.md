# ITMOSOFT Laravel Money

Shared monetary value, precision and rounding services for ITMOSOFT Laravel packages.

This package provides a consistent Laravel-facing entry point over [Brick Money](https://github.com/brick/money), avoiding separate monetary arithmetic and currency precision policies in each commerce package.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- `brick/money` ^0.15.2

## Installation

```bash
composer require itmosoft/laravel-money
php artisan vendor:publish --tag=itmosoft-money-config
```

Laravel package auto-discovery registers `MoneyServiceProvider`.

## Basic usage

```php
use ItmoSoft\Money\Services\MoneyManager;

final class InvoiceTotals
{
    public function __construct(private MoneyManager $money) {}

    public function total(): string
    {
        $subtotal = $this->money->of('125.50', 'USD');
        $tax = $this->money->of('10.04', 'USD');

        return (string) $subtotal->plus($tax); // USD 135.54
    }
}
```

The manager deliberately accepts integers and decimal strings, not PHP floats. Pass `'19.9999'`, not `19.9999`.

## Currency precision

ISO 4217 fraction digits from Brick Money are used by default. Examples: USD (2), JPY (0), KWD (3). Override scales in `config/itmosoft-money.php` only when your business domain requires it. Custom three-letter assets can be registered explicitly.

```php
'currency_scales' => ['USD' => 2, 'JPY' => 0, 'KWD' => 3],
'custom_currencies' => [
    'XBT' => ['name' => 'Bitcoin', 'scale' => 8, 'numeric_code' => null],
],
```

Use ISO currency codes where possible. A custom code must be three letters and must not conflict with an ISO code already used in the same application.

## Exact intermediate calculations

Use `exact()` to retain a rational intermediate amount and round once at a deliberate business boundary:

```php
$manager = app(MoneyManager::class);
$exactTax = $manager->exact('19.99', 'USD')
    ->multipliedBy('15')
    ->dividedBy('100');
$tax = $manager->settle($exactTax); // USD 3.00
```

For a chain of operations, retain `RationalMoney` until the amount is ready to store, display, or submit to a payment provider.

## Other operations

```php
$minor = $manager->ofMinor('1234', 'USD'); // USD 12.34
$discount = $manager->percentage($manager->exact('19.99', 'USD'), '15');
$total = $manager->sum([
    $manager->exact('0.335', 'USD'),
    $manager->exact('0.335', 'USD'),
    $manager->exact('0.335', 'USD'),
], 'USD'); // USD 1.01 after one settlement
```

`sum()` rejects mixed currencies and settles once after aggregation.

## Rounding

Default rounding is `HALF_UP`. Supported config values: `UP`, `DOWN`, `CEILING`, `FLOOR`, `HALF_UP`, `HALF_DOWN`, `HALF_EVEN`, `HALF_ODD`, `UNNECESSARY`.

A call can override the configured mode:

```php
use Brick\Math\RoundingMode;
$final = $manager->settle($exact, roundingMode: RoundingMode::HALF_EVEN);
```

## Integration rules

1. Do not use floats for monetary arithmetic. Convert legacy float inputs at system boundaries only after defining how their decimal representation is interpreted.
2. Keep intermediate calculations rational where chained operations would otherwise round repeatedly.
3. Settle at explicit boundaries: line total, tax total, invoice total or payment settlement.
4. Currency is part of the value. Do not add different currencies without an explicit FX rate and conversion policy.
5. Do not assume every currency has two fraction digits.
6. Database precision and data migrations remain the responsibility of each consuming package. This package does not modify existing schemas.

Recommended adoption order: Pricing and Cart; Checkout and Orders; Tax, Promotions and Shipping; Payments; Commerce Adapters.

## Tests

```bash
composer install
composer validate --no-check-publish
composer test
```

## License

MIT. See [LICENSE](LICENSE).
