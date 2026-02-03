# Contributing

- Fork the repo, clone, then run `composer install`.

## Before opening a pull request

- Run `composer check` – it runs PHPCS, Pint, Rector, PHPStan, tests with coverage, security audit, and Composer normalize (same steps as CI).
- Tests: `composer test`, coverage: `composer test:coverage`. Code changes require 100 % coverage.
- Code style: Pint and PHPCS per `ruleset.xml`. Auto-fix: `composer fix`.

## Pull request

- Clear description of the change, ideally linked to an issue (e.g. Closes #123).
- CI must pass (same checks as `composer check`).
