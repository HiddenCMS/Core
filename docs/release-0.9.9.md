# HiddenCMS Core 0.9.9

- Display the maintenance warning banner to every account allowed to bypass maintenance mode.
- Keep the site-opening action exclusive to administrators.
- Let delegated groups see a full-width informational banner without exposing maintenance settings.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, Composer metadata, translation audit and git diff checks pass. Maintenance and delegated-permission regression suites pass.
