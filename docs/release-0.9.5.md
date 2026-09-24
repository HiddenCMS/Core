# HiddenCMS Core 0.9.5

- Prevent a fatal error when the group service is not yet available while maintenance routing is initialized.
- Treat a missing or null user-group list as an anonymous visitor with no maintenance exemption.
- Keep administrator and configured-group maintenance access unchanged.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, Composer metadata and git diff checks pass. Seven maintenance-access scenarios pass, including the null group-list case reported on hosted installations.
