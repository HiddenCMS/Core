# HiddenCMS Core 0.9.1

- Complete outline assignments, inheritance and outline options in the layout builder.
- Restore TinyMCE in free-content widgets and bundle the editor assets required in production.
- Restore the media-library modal in widget editors and defer picker initialization until its script is available.
- Fix column sizing and reordering after adding or removing rows and columns.
- Fix modal closing and keep modal headers and actions visible while their content scrolls.
- Remove the obsolete iframe editor and its legacy assets.
- Require Altitude `^0.5` for the expanded theme customization interface.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no new migration.

## Validation

PHP syntax, Composer metadata, rich-editor sanitization, calendar form behavior and git diff checks pass. The layout builder and Altitude customization were also exercised locally in the administration interface.
