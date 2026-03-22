# Vendoring Wave 48 — Runtime Contracts and Statement Mailer Hardening

## What was fixed
- Declared the Symfony runtime packages that are already used by the source tree in `composer.json`.
- Hardened `StatementMailerService` around invalid email, unreadable attachment paths, and stable failure signaling.
- Added observability increments for statement mail success, invalid email, missing attachment, and transport failure.
- Added a unit test slice for the statement mailer and the monthly statement command.
- Extended smoke coverage so Composer/runtime checks fail fast when Symfony runtime packages are missing.

## Why this wave matters
Before this wave, the repository autoloaded Symfony classes from source code without declaring the matching runtime packages in Composer. That made the test stack non-reproducible even after the earlier PHPUnit/PHPStan setup.

## Files changed
- composer.json
- src/Service/Statement/StatementMailerService.php
- tests/Smoke/ComposerConfigurationSmokeTest.php
- tests/bin/smoke.php
- tests/Support/Statement/FakeMailer.php
- tests/Support/Statement/FakeStatementMailerService.php
- tests/Unit/Statement/StatementMailerServiceTest.php
- tests/Unit/Command/SendVendorStatementsCommandTest.php
