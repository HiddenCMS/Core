# HiddenCMS Core 0.9.8

- Fix the delegated administration landing redirect using the global addon model instead of the admin-scoped model.
- Prevent the erroneous lookup of a non-existent `admin_addon` table after signing in with a delegated account.
- Add an integration regression test covering authorized-module discovery from the delegated dashboard.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, Composer metadata, translation audit and git diff checks pass. Seventeen isolated permission checks pass, including delegated dashboard destination resolution through the core addon model.
