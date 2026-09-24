# HiddenCMS Core 0.9.2

- Add granular User administration permissions for viewing, creating, editing and deleting users, assigning user groups, managing groups, and managing sessions.
- Keep permission assignment and sign-in/custom-field configuration restricted to administrators.
- Prevent delegated managers from editing administrators or passing user-management privileges through privileged groups.
- Stop update checks from running during ordinary administration page rendering.
- Add `php tools/core.php updates-check` for scheduled Core and addon checks with a non-blocking execution lock and meaningful exit codes.
- Keep the manual Search action on the Updates page as an explicit cache refresh.
- Show a clear not-checked state before the first scheduled or manual update check.
- Document cron setup for hosting environments.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no new migration.

## Operations

Configure the hosting cron to run `php tools/core.php updates-check`, for example every 30 minutes. Ordinary administration requests now read only the cached status.

## Validation

PHP syntax, Composer metadata, translation audit, dashboard tests, isolated User-permission tests, update-check locking, manual refresh and cache-free administration rendering pass.
