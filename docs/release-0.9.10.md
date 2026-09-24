# HiddenCMS Core 0.9.10

- Preserve widget settings immediately after applying their configuration in the layout builder.
- Normalize serialized `settings[...]` values before widget validation and persistence.
- Add a regression test for widget settings submitted by the new layout builder.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no migration.

## Validation

PHP syntax, layout compatibility, widget settings persistence and git diff checks pass.
