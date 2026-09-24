# HiddenCMS Core 0.9.6

- Resolve persisted user groups directly during early maintenance routing, before the full group service is initialized.
- Restore maintenance access for authenticated members and database-backed custom groups such as editor roles.
- Keep administrator access implicit and exclude runtime-only module groups from the maintenance selector.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, Composer metadata, translation audit and git diff checks pass. Isolated permission tests verify implicit members, persisted custom-group resolution and maintenance access before group-service initialization.
