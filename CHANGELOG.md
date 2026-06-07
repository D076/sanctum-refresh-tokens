# Changelog

All notable changes to `sanctum-refresh-tokens` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.0.0] - 2026-06-08

### Added
- `abilities` column on `personal_refresh_tokens` (new migration) so a token's scope
  is persisted on the refresh token and survives across refreshes.
- Laravel 13 support (`illuminate/*: ^11.0|^12.0|^13.0`).
- Test suite: Pest + Orchestra Testbench, covering refresh-token hashing, rotation,
  replay protection, TTL enforcement, login/logout and password reset.
- Cross-database coverage — the suite runs against SQLite, PostgreSQL and MySQL.
- PHPStan + Larastan static analysis at **level 6** (`phpstan.neon`).
- GitHub Actions CI matrix (PHP 8.3/8.4/8.5 × Laravel 11/12/13) plus a cross-database job.
- `.gitattributes` to keep dev-only files out of the `composer require` dist archive.
- `LICENSE` file (MIT) and a rewritten, comprehensive `README.md`.

### Changed
- **BC:** minimum PHP raised to **8.3** (matches Laravel 13's requirement; drops 8.2).
- **BC:** declared dependencies explicitly — added `illuminate/auth`,
  `illuminate/notifications` and `laravel/framework` (the base `AuthenticatableUser`
  extends `Illuminate\Foundation\Auth\User`, which only ships within the framework).

### Fixed
- `AuthService::refresh()` reset the access token's abilities to `['*']`, widening a
  scoped token's permissions on every refresh. Abilities are now stored on the refresh
  token and carried over, so the scope is preserved even after the original (short-lived)
  access token has been pruned.
- `AuthService::getUserFromCredentials()` called `getEmailField()` on a class-string
  instead of an instance, which would fatal for models that define a custom email
  column; it is now resolved on a model instance.

## [3.1.0] - 2025-07-09
- Laravel 12 support.

## [3.0.0] - 2024-08-13
- Laravel 11 support and v3 API.

## [2.0.0] - 2024-03-14

## [1.0.0] - 2024-02-29
- Initial release.

[Unreleased]: https://github.com/d076/sanctum-refresh-tokens/compare/v4.0.0...HEAD
[4.0.0]: https://github.com/d076/sanctum-refresh-tokens/compare/v3.1.0...v4.0.0
[3.1.0]: https://github.com/d076/sanctum-refresh-tokens/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/d076/sanctum-refresh-tokens/compare/v2.0.0...v3.0.0
[2.0.0]: https://github.com/d076/sanctum-refresh-tokens/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/d076/sanctum-refresh-tokens/releases/tag/v1.0.0
