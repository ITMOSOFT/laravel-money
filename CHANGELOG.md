# Changelog

## [1.0.1] - Unreleased

### Fixed
- Align Brick Money and Brick Math constraints with the dependency range shared by Laravel 12 and 13.
- Use Brick Money APIs compatible with the 0.11 release line for exact settlement.
- Normalize missing custom-currency numeric codes to zero for Brick Money 0.11 compatibility.
- Remove the HALF_ODD config option, which is not available in the compatible Brick Math 0.14.x line.

## [1.0.0] - 2026-11-23

### Added
- Laravel 12 and 13 provider and configuration.
- Shared MoneyManager backed by Brick Money.
- ISO currency precision, custom currency registration and configurable scale overrides.
- Exact rational intermediate calculations and explicit settlement.
- Minor-unit creation, percentage calculation and currency-safe summation.
- Unit tests and GitHub Actions workflow.
