# HiddenCMS Core 0.9.7

- Add a dedicated permission allowing delegated groups to view site statistics.
- Apply statistics permissions to both the administration page and its AJAX requests.
- Show Users, Menus and Statistics navigation entries when the current account has the corresponding delegated permissions.
- Redirect delegated administrators from the restricted dashboard to their first authorized administration section.
- Decode stored HTML entities before displaying page titles and subtitles in the edition form.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, Composer metadata, translation audit and git diff checks pass. Permission, maintenance, statistics, dashboard and authentication regression suites pass, including delegated statistics access.
