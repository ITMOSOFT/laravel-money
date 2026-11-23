# Repository and release instructions

## Suggested repository
- Name: `ITMOSOFT/laravel-money`
- Description: `Shared monetary value, precision and rounding services for ITMOSOFT Laravel packages.`

## Validate locally
```bash
composer validate --no-check-publish
composer test
```

## User-run initial commit and push
```bash
git add .
git commit -m "feat: create shared Laravel money package"
git branch -M main
git remote add origin git@github.com:ITMOSOFT/laravel-money.git
git push -u origin main
```
If the remote already exists, do not add it again.

## User-run tag (only after CI passes)
```bash
git tag -a v0.1.0 -m "Release v0.1.0"
git push origin v0.1.0
```

## GitHub Release
Title: `v0.1.0 – Initial Shared Money Package`

Suggested notes:
```markdown
## What's Included
- Shared Laravel MoneyManager backed by Brick Money.
- ISO currency fraction digits with configurable overrides.
- Exact rational intermediate calculations and explicit settlement.
- Minor-unit creation, percentage calculations and currency-safe sums.
- Laravel 12 and 13 support.

## Requirements
- PHP 8.3+
- Laravel 12 or 13

## Installation
composer require itmosoft/laravel-money
```

## Packagist
1. Create/import `ITMOSOFT/laravel-money` on GitHub.
2. Submit the repository URL to Packagist.
3. Enable the Packagist GitHub webhook.
4. After pushing `v0.1.0`, confirm Packagist exposes the release.
5. Install in clean Laravel 12 and Laravel 13 applications.

No GitHub or Packagist action is performed by this generation step.
