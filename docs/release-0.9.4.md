# HiddenCMS Core 0.9.4

- Allow administrators to select user groups that may continue browsing the website during maintenance mode.
- Keep administrator access implicit so maintenance settings can never lock administrators out.
- Apply maintenance exemptions immediately after authentication to members and custom groups.
- Validate saved group identifiers against the groups currently available on the site.
- Add the maintenance group setting to fresh installations and create it lazily when an existing site first saves the maintenance form.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no mandatory migration.

## Validation

PHP syntax, Composer metadata, translation audit and git diff checks pass. Six maintenance-access scenarios pass, and the group selection was saved and reloaded through the administration interface.
