# HiddenCMS 0.8.1

- Fix disabling built-in optional modules, including Comments, Pages and Search. Mandatory modules remain enabled.
- Disable Comments by default on new installations; preserve existing sites' settings and comment data.
- During core updates, keep incoming release constraints for core-owned Composer dependencies while preserving additional site addons.
- Add regression tests for addon activation, fresh installation defaults and dependency merging.

## Compatibility

PHP >=8.1; Altitude ^0.4. Database schema remains 0.8.0; no new migration.

Sites still on Core 0.7.x run their existing updater during the upgrade. If it retains an Altitude ^0.3 constraint, change the site's composer.json requirement to "^0.3 || ^0.4" before retrying the core update. Do not install Altitude 0.4 separately on Core 0.7.

## Validation

Activation tests, isolated database installation, dependency-merge regression tests, PHP syntax and git diff checks pass.
