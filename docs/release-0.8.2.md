# HiddenCMS Core 0.8.2

- Fix HTTP 500 during web installer system checks caused by the removed HIDDENCMS_VERSION_CHECK_URL constant.
- Remove the obsolete update-service probe from installation compatibility checks. Core updates remain available in administration.
- Add an isolated regression test for system checks without legacy version constants, with no HTTP requests, mail or filesystem writes.

## Compatibility

PHP >=8.1; Altitude ^0.4. Database schema remains 0.8.0; no new migration.

## Validation

Installer system-check regression test, PHP syntax and git diff checks pass. Hosted installation has not been tested directly.
