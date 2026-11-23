# Repository and release instructions

## Repository
- Name: `ITMOSOFT/laravel-money`
- Description: `Shared monetary value, precision and rounding services for ITMOSOFT Laravel packages.`

## Validate locally
Run from the package root:

```bash
composer validate --no-check-publish
composer update --with-all-dependencies --prefer-dist
composer test
```

The GitHub Actions matrix must pass for both PHP 8.3 / Laravel 12 and PHP 8.3 / Laravel 13 before releasing.

## Patch release 1.0.1
This patch resolves the Brick Money / Brick Math dependency conflict that prevented Laravel 12 from installing the package. Do not modify or recreate the existing `v1.0.0` tag.

Suggested commit message: `fix: support Laravel 12 Brick Math constraints`

User-run commands:

```bash
git add composer.json README.md CHANGELOG.md RELEASE.md src/MoneyServiceProvider.php src/Services/MoneyManager.php src/Services/CurrencyPrecisionRegistry.php
git commit -m "fix: support Laravel 12 Brick Math constraints"
git push origin main
git tag -a v1.0.1 -m "Release v1.0.1"
git push origin v1.0.1
```

GitHub Release title: `v1.0.1 – Laravel 12 dependency compatibility`

Suggested release notes:

```markdown
## Fixed
- Resolve the Brick Money / Brick Math dependency conflict in Laravel 12.
- Keep Laravel 12 and 13 compatibility with Brick Money 0.11.2 and Brick Math 0.14.x.
- Make settlement and custom currency construction compatible with the selected Brick Money API.
- Remove HALF_ODD from the supported rounding configuration because Brick Math 0.14.x does not provide that enum case.

## Requirements
- PHP 8.3+
- Laravel 12 or 13

## Upgrade
```bash
composer update itmosoft/laravel-money --with-all-dependencies
```
```

## Packagist
After pushing the tag, confirm Packagist exposes `1.0.1`, then test installation in clean Laravel 12 and Laravel 13 applications.

No GitHub or Packagist action is performed by this package preparation.
